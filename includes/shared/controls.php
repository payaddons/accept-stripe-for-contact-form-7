<?php

namespace CF7PA_Pay_Addons\Shared;

use CF7PA_Pay_Addons\Stripe\Stripe_Settings;
use CF7PA_Pay_Addons\Stripe\Stripe_Helper;
use CF7PA_Pay_Addons\Shared\Utils;

class Controls {

  public static function get_session_checkout_setting_fields($settings) {
    $default_currency = Stripe_Settings::get_setting('default_currency');
		$countries = Countries::get_countries();
		$pages_options = ['' => __('Current form page', 'contact-form-7-stripe-addon')];
		$pages_options += Utils::get_pages_options();

    return [
      'save_metadata' => [
        'type' => 'checkbox',
        'label' => 'Save as metadata',
        'value' => $settings['save_metadata'],
        'desc_tip' => true,
        'description' => 'Store form data directly within Stripe metadata, with a limit of up to 50 fields.'
      ],
      'payment_method_types' => [
        'type' => 'multiselect',
        'label' => 'Payment Methods',
        'value' => $settings['payment_method_types'],
        'options' => Utils::get_supported_payment_methods($default_currency),
        'desc_tip' => true,
        'description' => 'Select supported payment methods'
      ],
      'success_url' => [
        'type' => 'dropdown',
        'label' => __('Success URL', 'contact-form-7-stripe-addon'),
        'value' => $settings['success_url'],
        'options' => $pages_options,
        'desc_tip' => true,
        'description' => __('Select the page to redirect to after successful payment', 'contact-form-7-stripe-addon'),
      ],
      'cancel_url' => [
        'type' => 'dropdown',
        'label' => __('Failed URL', 'contact-form-7-stripe-addon'),
        'value' => $settings['cancel_url'],
        'options' => $pages_options,
        'desc_tip' => true,
        'description' => __('Select the page to redirect to after failed payment', 'contact-form-7-stripe-addon'),
      ],
      'submit_type' => [
        'type' => 'dropdown',
        'label' => 'Submit button type',
        'value' => $settings['submit_type'],
        'options' => [
          'auto' => esc_html__('auto', 'contact-form-7-stripe-addon'),
          'pay' => esc_html__('pay', 'contact-form-7-stripe-addon'),
          'book' => esc_html__('book', 'contact-form-7-stripe-addon'),
          'donate' => esc_html__('donate', 'contact-form-7-stripe-addon'),
        ],
      ],
      'billing_address_collection' => [
        'type' => 'checkbox',
        'label' => esc_html__('Billing address required', 'contact-form-7-stripe-addon'),
        'value' => $settings['billing_address_collection'],
      ],
      'allow_promotion_codes' => [
        'type' => 'checkbox',
        'label' => esc_html__('Enable promotion', 'contact-form-7-stripe-addon'),
        'value' => $settings['allow_promotion_codes'],
      ],
      'automatic_tax' => [
        'type' => 'checkbox',
        'label' => esc_html__('Enable automatic taxes', 'contact-form-7-stripe-addon'),
        'value' => $settings['automatic_tax'],
      ],
      'tax_behavior' => [
        'type' => 'dropdown',
        'label' =>  esc_html__('Tax Behaviors', 'contact-form-7-stripe-addon'),
        'value' => $settings['tax_behavior'],
        'options' => [
          'inclusive' => esc_html__('Inclusive', 'contact-form-7-stripe-addon'),
          'exclusive' => esc_html__('Exclusive', 'contact-form-7-stripe-addon'),
        ],
      ],
      'phone_number_collection' => [
        'type' => 'checkbox',
        'label' => esc_html__('Phone number required', 'contact-form-7-stripe-addon'),
        'value' => $settings['phone_number_collection'],
      ],
      'terms_of_service' => [
        'type' => 'checkbox',
        'label' => esc_html__('Enable terms of service', 'contact-form-7-stripe-addon'),
        'value' => $settings['terms_of_service'],
      ],
      'shipping_address_collection' => [
        'type' => 'multiselect',
        'label' =>  esc_html__('Shipping address countries', 'contact-form-7-stripe-addon'),
        'value' => $settings['shipping_address_collection'],
        'options' => $countries,
      ],
    ];
  }

  public static function get_payment_type_condition_fields($settings) {
    return [
      'payment_type_condition_field' => [
        'type' => 'text',
        'label' => esc_html__('Condition Field', 'contact-form-7-stripe-addon'),
        'value' => $settings['payment_type_condition_field'],
        'placeholder' => "[some-field-name]",
        'description' => esc_html__('This field is decide to use subscrition or not. such as [is-subscription]', 'contact-form-7-stripe-addon'),
      ],
      'payment_type_condition_operation' => [
        'type' => 'dropdown',
        'label' =>  esc_html__('Condition Operation', 'contact-form-7-stripe-addon'),
        'value' => $settings['payment_type_condition_operation'],
        'options' => [
          'checked' => esc_html__('Checked', 'contact-form-7-stripe-addon'),
          'equalto' => esc_html__('Equal to', 'contact-form-7-stripe-addon'),
          'empty' => esc_html__('Empty', 'contact-form-7-stripe-addon'),
          'notchecked' => esc_html__('Not checked', 'contact-form-7-stripe-addon'),
          'notempty' => esc_html__('Not empty', 'contact-form-7-stripe-addon'),
          'notequalto' => esc_html__('Not equal to', 'contact-form-7-stripe-addon'),
        ],
      ],
      'payment_type_condition_value' => [
        'type' => 'text',
        'label' => esc_html__('Condition Value', 'contact-form-7-stripe-addon'),
        'value' => $settings['payment_type_condition_value'],
      ],
    ];
  }

  public static function get_one_time_pricing_fields($settings) {
		$currencies = Utils::get_currencies_options();
    return [
      'onetime_currency' => [
        'type' => 'dropdown',
        'label' => esc_html__('Currency', 'contact-form-7-stripe-addon'),
        'value' => $settings['onetime_currency'],
        'options' => $currencies,
      ],
      'onetime_amount_field' => [
        'type' => 'text',
        'label' => esc_html__('Amount Field', 'contact-form-7-stripe-addon'),
        'value' => $settings['onetime_amount_field'],
        'placeholder' => esc_html__('19.9 or [your-amount]', 'contact-form-7-stripe-addon'), 
        'description' => __('a fixed amount or a form field such as [amount]', 'contact-form-7-stripe-addon'),
      ],
      'onetime_quantity_field' => [
        'type' => 'text',
        'label' => esc_html__('Quantity Field', 'contact-form-7-stripe-addon'),
        'value' => $settings['onetime_quantity_field'],
        'description' => __('a fixed quantity or a form field such as [quantity]', 'contact-form-7-stripe-addon'),
      ],
      'onetime_product_name_field' => [
        'type' => 'text',
        'label' => esc_html__('Product Name Field', 'contact-form-7-stripe-addon'),
        'value' => $settings['onetime_product_name_field'],
        'description' => __('a fixed value or including a form field such as [product-name]', 'contact-form-7-stripe-addon'),
      ],
      'onetime_product_desc_field' => [
        'type' => 'text',
        'label' => esc_html__('Product Description Field', 'contact-form-7-stripe-addon'),
        'value' => $settings['onetime_product_desc_field'],
        'description' => __('a fixed value or including a form field such as [product-desc]', 'contact-form-7-stripe-addon'),
      ],
    ];
  }

  public static function get_sub_pricing_fields($settings) {
		$currencies = Utils::get_currencies_options();
    return [
      'sub_currency' => [
        'type' => 'dropdown',
        'label' => esc_html__('Currency', 'contact-form-7-stripe-addon'),
        'value' => $settings['sub_currency'],
        'options' => $currencies,
      ],
      'sub_amount_field' => [
        'type' => 'text',
        'label' => esc_html__('Amount Field', 'contact-form-7-stripe-addon'),
        'value' => $settings['sub_amount_field'],
        'placeholder' => "19.9 or [amount]",
        'description' => esc_html__('a fixed value or a form field such as [amount]', 'contact-form-7-stripe-addon'),
      ],
      'sub_quantity_field' => [
        'type' => 'text',
        'label' => esc_html__('Quantity Field', 'contact-form-7-stripe-addon'),
        'value' => $settings['sub_quantity_field'],
        'placeholder' => "1 or [quantity]",
        'description' => esc_html__('a fixed value or a form field such as [quantity]', 'contact-form-7-stripe-addon'),
      ],
      'sub_interval_count_field' => [
        'type' => 'text',
        'label' => esc_html__('Number of intervals Field', 'contact-form-7-stripe-addon'),
        'value' => $settings['sub_interval_count_field'],
        'placeholder' => "3 or [interval-count]",
        'description' => esc_html__('a fixed value or a form field such as [interval-count]', 'contact-form-7-stripe-addon'),
      ],
      'sub_interval_field' => [
        'type' => 'text',
        'label' => esc_html__('Recurring frequency Field', 'contact-form-7-stripe-addon'),
        'value' => $settings['sub_interval_field'],
        'placeholder' => "month or [mouth]",
        'description' => esc_html__('Either day, week, month or year or any specified field such as [interval]', 'contact-form-7-stripe-addon'),
      ],
      'sub_product_name_field' => [
        'type' => 'text',
        'label' => esc_html__('Product Name Field', 'contact-form-7-stripe-addon'),
        'value' => $settings['sub_product_name_field'],
        'description' => __('a fixed value or a form field such as [product-name]', 'contact-form-7-stripe-addon'),
      ],
      'sub_product_desc_field' => [
        'type' => 'text',
        'label' => esc_html__('Product Description Field', 'contact-form-7-stripe-addon'),
        'value' => $settings['sub_product_desc_field'],
        'description' => __('a fixed value or a form field such as [product-desc]', 'contact-form-7-stripe-addon'),
      ],
    ];
  }
}