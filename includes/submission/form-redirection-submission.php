<?php

namespace CF7PA_Pay_Addons\submission;

if (! defined('ABSPATH')) {
	exit();
}

// Exit if accessed directly

use CF7PA_Pay_Addons\Stripe\Stripe_API;
use CF7PA_Pay_Addons\Shared\Logger;
use CF7PA_Pay_Addons\Shared\Utils;
use CF7PA_Pay_Addons\Admin\CF7\Checkout_Redirect_Setting;

class Form_Redirection_Submission extends Base_Submission
{

	public function __construct($contact_form)
	{
		parent::__construct($contact_form);
	}

	final function get_contact_form_settings() {
		$form_id = $this->get_contact_form()->id();
		return get_post_meta($form_id, Checkout_Redirect_Setting::$_setting_id, true);
	}

	public function process(&$response, $submission)
	{
		// Get form ID
		$form_id = $this->get_contact_form()->id();

		if(!self::is_submission_enable($form_id, Checkout_Redirect_Setting::$_setting_id)) {
			return;
		}
		// Get form data
		$form_data = $submission->get_posted_data();
		// append form id
		$form_data['form_id'] = $form_id;	
		
		// Store POST data in transient for check status
		self::store_post_data_by_session();
		
		// Create Checkout Session
		try {
			$redirect_link = '';

			$form_settings = $this->get_contact_form_settings();
			$is_stripe_link = $form_settings['enable_link'] == 'yes';
			if ($is_stripe_link) {
				$redirect_link = $form_settings['stripe_link'];
			} else {
				$checkout_session = $this->create_session_checkout($form_data);
				$redirect_link = $checkout_session->url;
				
				// Store POST data in transient for webhook later use
				$this->store_post_data($checkout_session->id);
			}
			$response['status'] = 'stripe-checkout-success';
			$response['redirectUrl'] = $redirect_link;
		} catch (\Error $e) {
			// Handle errors
			$response['status'] = 'stripe-invalid';
			$response['message'] = $e->getMessage();

			Logger::error('create session checkout failed:' . $e->getMessage());
		}
	}

	public function create_session_checkout($contact_form_data) {
		$session_checkout_args = $this->collect_session_checkout_args($contact_form_data);

		$session_checkout = Stripe_API::create_checkout_session($session_checkout_args);

		return $session_checkout;
	}

	protected function collect_session_checkout_args($contact_form_data) {
		$form_settings = $this->get_form_settings($contact_form_data);

		$is_subscription = $this->is_subscription($form_settings);

		$save_metadata = isset($form_settings['save_metadata']) && $form_settings['save_metadata'] == 'yes';

		$line_item = [
			'quantity' => floatval($form_settings[
				$is_subscription ? 'sub_quantity_field' : 'onetime_quantity_field'
			]),
		];
		if($is_subscription && $form_settings['sub_enable_pricing_plan'] == 'yes') {
			$price_id = $form_settings['sub_price_id_field'];
			if(empty($price_id)) {
				$price_id = $form_settings['sub_price_id'];
			}
			$line_item['price'] = $price_id;
		}
		else {
			$currency = strtolower($form_settings[
				$is_subscription ? 'sub_currency' : 'onetime_currency'
			]);
			$unit_amount = floatval($form_settings[
				$is_subscription ? 'sub_amount_field' : 'onetime_amount_field'
			]);
			$name = $form_settings[
				$is_subscription ? 'sub_product_name_field' : 'onetime_product_name_field'
			];
			$line_item['price_data'] = [
				'currency' => $currency,
				'unit_amount' => $unit_amount * 100,
				'product_data' => [
					'name' => $name,
				],
			];

			$desc =	$form_settings[
				$is_subscription ? 'sub_product_desc_field' : 'onetime_product_desc_field'
			]; 
			if(!empty($desc)) {
				$line_item['price_data']['product_data']['description'] = $desc;
			}

			if ($is_subscription) {
				$line_item['price_data']['recurring'] = [
					'interval' => strtolower($form_settings['sub_interval_field']),
					'interval_count' => floatval($form_settings['sub_interval_count_field']),
				];
			}

			if ($form_settings['automatic_tax'] == 'yes') {
				$line_item['price_data']['tax_behavior'] = $form_settings['tax_behavior'];
			}
		}

		$success_url = $this->get_current_url();
		$cancel_url = $this->get_current_url();
		if(!empty($form_settings['success_url'])) {
			$success_url = $form_settings['success_url'];
		}
		if(!empty($form_settings['cancel_url'])) {
			$cancel_url = $form_settings['cancel_url'];
		}

		$checkout_session = [
			'payment_method_types' => $form_settings['payment_method_types'],
			'line_items' => [$line_item],
			'mode' => $is_subscription ? 'subscription' : 'payment',
			'success_url' => add_query_arg([
				'session_id' => '{CHECKOUT_SESSION_ID}',
			], esc_url_raw($success_url)),
			'cancel_url' => esc_url_raw($cancel_url),
			'billing_address_collection' => $form_settings['billing_address_collection'] == 'yes' ? 'required' : 'auto',
			'allow_promotion_codes' => $form_settings['allow_promotion_codes'] == 'yes' ? true : false,
		];

		if ($form_settings['automatic_tax'] == 'yes') {
			$checkout_session['automatic_tax'] = ['enabled' => true];
			$checkout_session['tax_id_collection'] = [
				'enabled' => true,
				'required' => 'if_supported',
			];
		}

		if(in_array('automatic', $checkout_session['payment_method_types'])) {
			unset($checkout_session['payment_method_types']);
		}

		if(!empty($form_settings['email_field'])) {
			$checkout_session['customer_email'] = $form_settings['email_field']; 
		}

		if($checkout_session['mode'] == 'payment' && !empty($form_settings['submit_type'])) {
			$checkout_session['submit_type'] = $form_settings['submit_type'];
		}

		if($form_settings['phone_number_collection'] == 'yes') {
			$checkout_session['phone_number_collection'] = [ 'enabled' => true ];
		}

		if ($form_settings['terms_of_service'] == 'yes') {
			$checkout_session['consent_collection'] = [
        'terms_of_service' => $form_settings['terms_of_service'] == 'yes' ? 'required' :'none'
			];
		}

		if (!empty($form_settings['shipping_address_collection'])) {
			$checkout_session['shipping_address_collection'] = [
				'allowed_countries' => $form_settings['shipping_address_collection']
			];	
		}
		
		$checkout_session['customer_creation'] = 'always';
		$checkout_session['payment_intent_data'] = [];
		$checkout_session['subscription_data'] = [];
		$metadata = $this->get_form_metadata($contact_form_data, $save_metadata);
		if (!$is_subscription && !empty($metadata)) {
			$checkout_session['payment_intent_data']['metadata'] = $metadata;
		}
		if ($is_subscription && !empty($metadata)) {
			$checkout_session['subscription_data']['metadata'] = $metadata;
		}
		$checkout_session['metadata'] = $metadata;

		if($name) {
			$description = Utils::format_stripe_desc($name, $desc);
			if(!$is_subscription) {
				$checkout_session['payment_intent_data']['description'] = $description;
			}
			if($is_subscription) {
				$checkout_session['subscription_data']['description'] = $description;
			}
		}
		return $checkout_session;	
	}
}
