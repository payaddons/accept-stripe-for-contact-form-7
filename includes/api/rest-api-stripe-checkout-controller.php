<?php

namespace CF7PA_Pay_Addons\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Exit if accessed directly
use CF7PA_Pay_Addons\Stripe\Stripe_API;
use CF7PA_Pay_Addons\Shared\Security_Utils;
use CF7PA_Pay_Addons\Shared\Logger;
use CF7PA_Pay_Addons\Admin\CF7\Checkout_Redirect_Setting;
use CF7PA_Pay_Addons\submission\Form_Redirection_Submission;

class Rest_API_Stripe_Checkout_Controller extends \WP_REST_Controller {
	public function __construct() {
		$this->namespace = CF7PA_ADDONS_REST_API . 'stripe';
		$this->rest_base = 'checkout-form';
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/retrieve_checkout_session',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'retrieve_checkout_session' ),
					'permission_callback' => array( $this, 'verify_access' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<id>\d+)/submit',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'handle_form_submit'),
					'permission_callback' => array($this, 'verify_access'),
				),
			)
		);
	}

	public function verify_access( \WP_REST_Request $request ) {
		return Security_Utils::client_access_check( $request );
	}

	public function retrieve_checkout_session( \WP_REST_Request $request ) {
		$stored_data = Form_Redirection_Submission::get_post_data_by_session();
		if ($stored_data && isset($stored_data['_wpcf7'])) {
			$form_id = $stored_data['_wpcf7'];
		}
		// Retrieve the Contact Form 7 form messages
		$form = \WPCF7_ContactForm::get_instance($form_id);
		if (!$form) {
			return new \WP_Error('form_not_found', __('Contact form not found' . $form_id), array('status' => 404));
		}

		if(!Form_Redirection_Submission::is_submission_enable($form_id, Checkout_Redirect_Setting::$_setting_id)) {
			return new \WP_Error('form_checkout_disabled', __('form checkout is disabled' . $form_id), array('status' => 401));
		}

		$messages = $form->prop('messages');
		if (empty($messages)) {
			$messages = wpcf7_messages();
		}

		$postData = $request->get_params();
		$session_id = sanitize_text_field( $postData['session_id'] );
		try {
			$session = Stripe_API::retrieve_checkout_session( $session_id, [ 'expand' => ['payment_intent'] ] );
			$payment_status = $session->payment_intent->status ?? $session->payment_status;
			if (($payment_status == 'paid' || $payment_status == 'succeeded') && $session->payment_link) {
				return $this->handle_payment_link_callback($stored_data, $messages);
			}
			return new \WP_REST_Response( [
				'status' => $payment_status,
				'messages' => $messages,
				'form_id' => (int)$form_id,
			] );
		} catch ( \Exception $ex ) {
			Logger::error( 'retrieve_checkout_session ' . $ex->getMessage() );
			return new \WP_Error( 'stripe_error', __( $ex->getMessage() ), array( 'status' => 400 ) );
		}
	}

	public function handle_payment_link_callback($form_data, $messages) {
		$form_id = $form_data['_wpcf7'];
		$form_settings = get_post_meta($form_id, Checkout_Redirect_Setting::$_setting_id, true);
		$is_stripe_link = $form_settings['enable_link'] == 'yes';

		if(!$is_stripe_link) return;

		$_POST = $form_data;

		// Trigger Contact Form 7 submission

		$contact_form = wpcf7_contact_form($form_id);
		if ($contact_form) {
			// Submit the form
			$result = $contact_form->submit();
			if ($result['status'] === 'mail_sent') {
				Form_Redirection_Submission::clear_session_transient();
				return new \WP_REST_Response( [
					'status' => 'succeeded',
					'messages' => $messages,
					'form_id' => (int)$form_id,
				] );
			}
			else {
				Logger::error('email sent failed: ' . $result['message']);
				return new \WP_Error( 'stripe_error', $result['message'], array( 'status' => 400 ) );
			}
		} else {
			Logger::error('Contact form not found for ID: ' . $form_id);
			return new \WP_Error('stripe_error', 'Contact form not found for ID: ' . $form_id, array('status' => 404));
		}
	}

	public function handle_form_submit(\WP_REST_Request $request)
	{
		$url_params = $request->get_url_params();

		$item = null;

		if ( ! empty( $url_params['id'] ) ) {
			$item = wpcf7_contact_form( $url_params['id'] );
		}

		if ( ! $item ) {
			return new \WP_Error( 'wpcf7_not_found',
				__( "The requested contact form was not found.", 'contact-form-7' ),
				array( 'status' => 404 )
			);
		}

		$unit_tag = wpcf7_sanitize_unit_tag(
			$request->get_param( '_wpcf7_unit_tag' )
		);

		if ( empty( $unit_tag ) ) {
			return new \WP_Error( 'wpcf7_unit_tag_not_found',
				__( "There is no valid unit tag.", 'contact-form-7' ),
				array( 'status' => 400 )
			);
		}

    // Simulate form submission
    $submission = \WPCF7_Submission::get_instance($item, array(
        'skip_mail' => true
    ));

		$result = array(
			'contact_form_id' => $item->id(),
		);

		$result += $submission->get_result();

		$response = array_merge( $result, array(
			'into' => sprintf( '#%s', $unit_tag ),
			'invalid_fields' => array(),
		) );

		if ( ! empty( $result['invalid_fields'] ) ) {
			$invalid_fields = array();

			foreach ( (array) $result['invalid_fields'] as $name => $field ) {
				if ( ! wpcf7_is_name( $name ) ) {
					continue;
				}

				$name = strtr( $name, '.', '_' );

				$invalid_fields[] = array(
					'field' => $name,
					'message' => $field['reason'],
					'idref' => $field['idref'],
					'error_id' => sprintf(
						'%1$s-ve-%2$s',
						$unit_tag,
						$name
					),
				);
			}

			$response['invalid_fields'] = $invalid_fields;
		}

		if(count($response['invalid_fields']) === 0) {
			$checkout_submission_name = $request->get_param( '_cf7pa_submission' );
			$supported_payment_methods = $request->get_param( 'supported-payment-methods' );
			if($checkout_submission_name) {
				$submission_class = "CF7PA_Pay_Addons\\submission\\{$checkout_submission_name}";
				if (class_exists($submission_class)) {
					$checkout_submission = $submission_class::get_instance($item);
					if(!empty($supported_payment_methods)) {
						$checkout_submission->set_supported_payment_methods(explode('|', $supported_payment_methods));
					}
					$checkout_submission->process($response, $submission);
				} else {
					$response['status'] = 'stripe-invalid';
					$response['message'] = "Submission class not found: {$submission_class}";
				}
			}
		}

		return rest_ensure_response( $response );
	}
}
