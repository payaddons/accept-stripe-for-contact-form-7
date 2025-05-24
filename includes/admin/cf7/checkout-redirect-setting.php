<?php

namespace CF7PA_Pay_Addons\Admin\CF7;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
use CF7PA_Pay_Addons\Shared\Controls;
use CF7PA_Pay_Addons\Shared\Logger;
class Checkout_Redirect_Setting extends Settings_API {
    public static $_prefix = 'cf7pacr';

    public static $_setting_id = 'cf7pa_checkout_redirect_setting';

    public function __construct() {
        if ( is_admin() ) {
            add_filter(
                'wpcf7_editor_panels',
                array($this, 'wpcf7_editor_panels'),
                10,
                1
            );
            // Save settings
            add_action(
                'wpcf7_save_contact_form',
                array($this, 'save_cf7_checkout_redirect_setting'),
                10,
                3
            );
        }
    }

    function save_cf7_checkout_redirect_setting( $contact_form, $data, $context ) {
        $form_id = $contact_form->id();
        if ( !isset( $_POST['save_checkout_redirect_setting_nonce'] ) || !wp_verify_nonce( $_POST['save_checkout_redirect_setting_nonce'], 'save_checkout_redirect_setting' ) ) {
            return;
        }
        $payment_settings = $_POST[static::$_prefix];
        $settings = array(
            'enable'                           => $this->get_text_field_value( $payment_settings, 'enable', 'no' ),
            'enable_link'                      => $this->get_text_field_value( $payment_settings, 'enable_link', 'no' ),
            'stripe_link'                      => ( isset( $payment_settings['stripe_link'] ) ? esc_url_raw( $payment_settings['stripe_link'] ) : '' ),
            'payment_method_types'             => array_map( 'sanitize_text_field', $payment_settings['payment_method_types'] ),
            'save_metadata'                    => $this->get_text_field_value( $payment_settings, 'save_metadata', 'no' ),
            'payment_type'                     => sanitize_text_field( $payment_settings['payment_type'] ),
            'success_url'                      => ( isset( $payment_settings['success_url'] ) ? esc_url_raw( $payment_settings['success_url'] ) : '' ),
            'cancel_url'                       => ( isset( $payment_settings['cancel_url'] ) ? esc_url_raw( $payment_settings['cancel_url'] ) : '' ),
            'submit_type'                      => sanitize_text_field( $payment_settings['submit_type'] ),
            'automatic_tax'                    => $this->get_text_field_value( $payment_settings, 'automatic_tax', 'no' ),
            'tax_behavior'                     => sanitize_text_field( $payment_settings['tax_behavior'] ),
            'phone_number_collection'          => $this->get_text_field_value( $payment_settings, 'phone_number_collection', 'no' ),
            'terms_of_service'                 => $this->get_text_field_value( $payment_settings, 'terms_of_service', 'no' ),
            'allow_promotion_codes'            => $this->get_text_field_value( $payment_settings, 'allow_promotion_codes', 'no' ),
            'billing_address_collection'       => $this->get_text_field_value( $payment_settings, 'billing_address_collection', 'no' ),
            'shipping_address_collection'      => array_map( 'sanitize_text_field', $payment_settings['shipping_address_collection'] ?? [] ),
            'email_field'                      => ( isset( $payment_settings['email_field'] ) ? sanitize_text_field( $payment_settings['email_field'] ) : '' ),
            'onetime_currency'                 => ( isset( $payment_settings['onetime_currency'] ) ? sanitize_text_field( $payment_settings['onetime_currency'] ) : '' ),
            'onetime_amount_field'             => ( isset( $payment_settings['onetime_amount_field'] ) ? sanitize_text_field( $payment_settings['onetime_amount_field'] ) : '' ),
            'onetime_quantity_field'           => ( isset( $payment_settings['onetime_quantity_field'] ) ? sanitize_text_field( $payment_settings['onetime_quantity_field'] ) : '' ),
            'onetime_product_name_field'       => ( isset( $payment_settings['onetime_product_name_field'] ) ? sanitize_text_field( $payment_settings['onetime_product_name_field'] ) : '' ),
            'onetime_product_desc_field'       => ( isset( $payment_settings['onetime_product_desc_field'] ) ? sanitize_text_field( $payment_settings['onetime_product_desc_field'] ) : '' ),
            'sub_currency'                     => ( isset( $payment_settings['sub_currency'] ) ? sanitize_text_field( $payment_settings['sub_currency'] ) : '' ),
            'sub_amount_field'                 => ( isset( $payment_settings['sub_amount_field'] ) ? sanitize_text_field( $payment_settings['sub_amount_field'] ) : '' ),
            'sub_quantity_field'               => ( isset( $payment_settings['sub_quantity_field'] ) ? sanitize_text_field( $payment_settings['sub_quantity_field'] ) : '' ),
            'sub_product_name_field'           => ( isset( $payment_settings['sub_product_name_field'] ) ? sanitize_text_field( $payment_settings['sub_product_name_field'] ) : '' ),
            'sub_product_desc_field'           => ( isset( $payment_settings['sub_product_desc_field'] ) ? sanitize_text_field( $payment_settings['sub_product_desc_field'] ) : '' ),
            'sub_interval_count_field'         => ( isset( $payment_settings['sub_interval_count_field'] ) ? sanitize_text_field( $payment_settings['sub_interval_count_field'] ) : '' ),
            'sub_interval_field'               => ( isset( $payment_settings['sub_interval_field'] ) ? sanitize_text_field( $payment_settings['sub_interval_field'] ) : '' ),
            'payment_type_condition_field'     => ( isset( $payment_settings['payment_type_condition_field'] ) ? sanitize_text_field( $payment_settings['payment_type_condition_field'] ) : '' ),
            'payment_type_condition_operation' => ( isset( $payment_settings['payment_type_condition_operation'] ) ? sanitize_text_field( $payment_settings['payment_type_condition_operation'] ) : '' ),
            'payment_type_condition_value'     => ( isset( $payment_settings['payment_type_condition_value'] ) ? sanitize_text_field( $payment_settings['payment_type_condition_value'] ) : '' ),
        );
        // For new forms, we might need to save immediately after creation
        if ( empty( $form_id ) ) {
            add_action( 'wpcf7_after_create', function ( $contact_form ) use($settings) {
                update_post_meta( $contact_form->id(), self::$_setting_id, $settings );
            } );
        } else {
            update_post_meta( $form_id, self::$_setting_id, $settings );
        }
    }

    function wpcf7_editor_panels( $panels ) {
        $panels['cf7pacr-panel'] = array(
            'title'    => __( 'Stripe Checkout Redirection', 'contact-form-7-stripe-addon' ),
            'callback' => array($this, 'cf7_payment_settings_panel_html'),
        );
        return $panels;
    }

    function cf7_payment_settings_panel_html( $post ) {
        $form_id = $post->id();
        $settings = get_post_meta( $form_id, self::$_setting_id, true );
        $settings = wp_parse_args( $settings, array(
            'enable'                           => 'no',
            'enable_link'                      => 'no',
            'stripe_link'                      => '',
            'payment_type'                     => 'onetime',
            'payment_method_types'             => ['automatic'],
            'submit_type'                      => 'auto',
            'success_url'                      => '',
            'cancel_url'                       => '',
            'save_metadata'                    => 'yes',
            'billing_address_collection'       => 'yes',
            'allow_promotion_codes'            => 'yes',
            'automatic_tax'                    => 'yes',
            'tax_behavior'                     => 'exclusive',
            'phone_number_collection'          => 'no',
            'terms_of_service'                 => 'no',
            'shipping_address_collection'      => ['US'],
            'email_field'                      => '',
            'onetime_currency'                 => '',
            'onetime_amount_field'             => '19.9',
            'onetime_quantity_field'           => '1',
            'onetime_product_name_field'       => 'your product name',
            'onetime_product_desc_field'       => 'your product description',
            'sub_currency'                     => '',
            'sub_quantity_field'               => '1',
            'sub_amount_field'                 => '19.9',
            'sub_interval_count_field'         => '1',
            'sub_interval_field'               => 'month',
            'sub_product_name_field'           => 'your product name',
            'sub_product_desc_field'           => 'your product description',
            'payment_type_condition_field'     => '',
            'payment_type_condition_operation' => '',
            'payment_type_condition_value'     => '',
        ) );
        $form_fields = $post->scan_form_tags();
        ?>
		<div id="cf7pacr" class="cf7pa-form-table bg-white p-6 rounded-lg shadow-md">
			<div class="mb-8">
				<div class="space-y-4">
					<?php 
        $this->render_form_fields( [
            'enable'      => [
                'type'  => 'checkbox',
                'label' => 'Enable',
                'value' => $settings['enable'],
            ],
            'enable_link' => [
                'type'    => 'checkbox',
                'label'   => 'Enable Stripe Link',
                'value'   => $settings['enable_link'],
                'premium' => true,
            ],
            'stripe_link' => [
                'type'        => 'text',
                'label'       => 'Stripe Link',
                'value'       => $settings['stripe_link'],
                'description' => __( 'During generating this Stripe link, two items are required: <br/> 1. In the "After Payment" tab, select "Don\'t show confirmation page" to ensure customers are redirected back to your site. <br/>
2. Append the fixed parameter string \'?session_id={CHECKOUT_SESSION_ID}\' after your specify URL.', 'contact-form-7-stripe-addon' ),
                'premium'     => true,
            ],
        ] );
        ?>
				</div>
			</div>
			<div id="basic-section" class="mb-8">
				<h3 class="text-xl font-semibold mb-4">Basic Settings</h3>
				<div class="space-y-4">
					<?php 
        $this->render_form_fields( Controls::get_session_checkout_setting_fields( $settings ) );
        ?>
				</div>
			</div>
			<div id="pricing-section">
				<h3 class="text-xl font-semibold mb-4"><?php 
        echo esc_html__( 'Pricing Settings', 'contact-form-7-stripe-addon' );
        ?></h3>
				<div class="space-y-4">
					<?php 
        $this->render_form_fields( [
            'email_field'  => [
                'type'        => 'text',
                'label'       => esc_html__( 'Customer Email Field', 'contact-form-7-stripe-addon' ),
                'value'       => $settings['email_field'],
                'placeholder' => "[your-email]",
                'description' => esc_html__( 'a fixed value or a form field such as [your-email]', 'contact-form-7-stripe-addon' ),
            ],
            'payment_type' => [
                'type'        => 'dropdown',
                'label'       => esc_html__( 'Payment Type', 'contact-form-7-stripe-addon' ),
                'value'       => $settings['payment_type'],
                'options'     => [
                    'onetime'      => esc_html__( 'One-time', 'contact-form-7-stripe-addon' ),
                    'subscription' => esc_html__( 'Subscription (pro)', 'contact-form-7-stripe-addon' ),
                    'flex'         => esc_html__( 'Flex (pro)', 'contact-form-7-stripe-addon' ),
                ],
                'description' => esc_html__( 'One-time, Subscription, or Flex (decided by end-user)', 'contact-form-7-stripe-addon' ),
            ],
        ] );
        ?>
					<?php 
        ?>

					<div class="mt-8">
						<nav class="payment-type-tabs" aria-label="Tabs">
							<a href="#" class="payment-type-tab" data-tab="onetime"><?php 
        echo esc_html__( 'One-time', 'contact-form-7-stripe-addon' );
        ?></a>
							<a href="#" class="payment-type-tab" data-tab="subscription"><?php 
        echo esc_html__( 'Subscription (pro)', 'contact-form-7-stripe-addon' );
        ?></a>
						</nav>

						<div id="onetime-tab" class="payment-content mt-4">
							<div class="space-y-4">
								<?php 
        $this->render_form_fields( Controls::get_one_time_pricing_fields( $settings ) );
        ?>
							</div>
						</div>

						<div id="subscription-tab" class="payment-content mt-4 hidden">
							<?php 
        cf7pa_upgrade_link();
        ?>
						</div>
					</div>
				</div>
			</div>
		</div>
<?php 
        wp_nonce_field( 'save_checkout_redirect_setting', 'save_checkout_redirect_setting_nonce' );
    }

}
