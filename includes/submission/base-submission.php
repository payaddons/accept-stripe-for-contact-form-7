<?php

namespace CF7PA_Pay_Addons\submission;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
use CF7PA_Pay_Addons\Stripe\Stripe_API;
use CF7PA_Pay_Addons\Shared\Logger;
use CF7PA_Pay_Addons\Shared\Utils;
class Base_Submission {
    private static $instance;

    protected $contact_form;

    static $session_prefix = 'cf7pa_user_data_';

    protected $supported_payment_methods;

    public function __construct( $contact_form ) {
        $this->contact_form = $contact_form;
    }

    protected function get_contact_form() {
        return $this->contact_form;
    }

    public static function get_instance( $contact_form = null, $options = '' ) {
        if ( $contact_form instanceof \WPCF7_ContactForm ) {
            if ( empty( self::$instance ) ) {
                self::$instance = new static($contact_form, $options);
                return self::$instance;
            } else {
                return null;
            }
        } else {
            if ( empty( self::$instance ) ) {
                return null;
            } else {
                return self::$instance;
            }
        }
    }

    public function set_supported_payment_methods( $payment_methods ) {
        $this->supported_payment_methods = $payment_methods;
    }

    protected function get_current_url() {
        $current_url = wp_get_referer();
        // If wp_get_referer() returns false, fallback to home_url()
        if ( !$current_url ) {
            $current_url = home_url( add_query_arg( array() ) );
        }
        return $current_url;
    }

    protected function is_subscription( $form_settings ) {
        return false;
    }

    // to be override
    protected function get_contact_form_settings() {
        $form_id = $this->get_contact_form()->id();
        return get_post_meta( $form_id, 'cf7pa_checkout_redirect_setting', true );
    }

    protected function get_form_settings( $contact_form_data ) {
        $contact_form_seting = $this->get_contact_form_settings();
        $processed_fields = [];
        $field_types = [
            'email_field',
            'onetime_currency_field',
            'onetime_quantity_field',
            'onetime_amount_field',
            'onetime_product_name_field',
            'onetime_product_desc_field',
            'sub_quantity_field',
            'sub_amount_field',
            'sub_interval_count_field',
            'sub_interval_field',
            'sub_product_name_field',
            'sub_product_desc_field',
            'payment_type_condition_field'
        ];
        foreach ( $field_types as $field_type ) {
            if ( isset( $contact_form_seting[$field_type] ) ) {
                $field_value = $contact_form_seting[$field_type];
                $value = $field_value;
                // Process the field value with mixed content
                $value = preg_replace_callback( '/\\[([^\\]]+)\\]/', function ( $matches ) use($contact_form_data) {
                    $field_name = $matches[1];
                    $replacement = ( isset( $contact_form_data[$field_name] ) ? $contact_form_data[$field_name] : '' );
                    // Handle array values
                    if ( is_array( $replacement ) && count( $replacement ) > 0 ) {
                        $replacement = $replacement[0];
                    }
                    return $replacement;
                }, $field_value );
                $processed_fields[$field_type] = $value;
            }
        }
        return array_merge( $contact_form_seting, $processed_fields );
    }

    protected function get_form_metadata( $contact_form_data, $save_metadata = true ) {
        $metadata = [];
        if ( $save_metadata ) {
            // Get required fields
            $required_fields = $this->get_required_fields();
            // Build final data array
            $metadata = $this->build_form_data( $contact_form_data, $required_fields );
        }
        $metadata['referer'] = $this->get_site_domain();
        return $metadata;
    }

    protected function get_required_fields() {
        $required_fields = [];
        $form_tags = wpcf7_scan_form_tags();
        foreach ( $form_tags as $tag ) {
            if ( $tag->is_required() && !empty( $tag->name ) ) {
                $required_fields[] = $tag->name;
            }
        }
        return $required_fields;
    }

    protected function build_form_data( $contact_form_data, $required_fields ) {
        $reduced_form_data = [];
        // Define internal helper functions as closures
        $process_string_value = function ( $value ) {
            if ( !is_string( $value ) ) {
                return $value;
            }
            return ( strlen( $value ) > 500 ? substr( $value, 0, 500 ) : $value );
        };
        $process_field_value = function ( $value ) use($process_string_value) {
            if ( is_array( $value ) && !empty( $value ) ) {
                return $process_string_value( $value[0] );
            } elseif ( is_string( $value ) && $value !== '' ) {
                return $process_string_value( $value );
            }
            return null;
        };
        $should_skip_field = function ( $field_name ) use($reduced_form_data) {
            return isset( $reduced_form_data[$field_name] ) || count( $reduced_form_data ) >= 50;
        };
        // Process required fields first
        foreach ( $required_fields as $field_name ) {
            if ( isset( $contact_form_data[$field_name] ) ) {
                $processed_value = $process_field_value( $contact_form_data[$field_name] );
                if ( $processed_value !== null ) {
                    $reduced_form_data[$field_name] = $processed_value;
                }
            }
        }
        // Process remaining fields until limit
        foreach ( $contact_form_data as $field_name => $value ) {
            if ( $should_skip_field( $field_name ) ) {
                continue;
            }
            $processed_value = $process_field_value( $value );
            if ( $processed_value !== null ) {
                if ( count( $reduced_form_data ) > 48 ) {
                    break;
                }
                $reduced_form_data[$field_name] = $processed_value;
            }
        }
        return $reduced_form_data;
    }

    protected function get_site_domain() {
        return parse_url( get_site_url(), PHP_URL_HOST );
    }

    public function store_post_data( $key ) {
        set_transient( $key, $_POST );
    }

    static function is_submission_enable( $form_id, $form_setting_id ) {
        // Get payment settings
        $form_settings = get_post_meta( $form_id, $form_setting_id, true );
        // Check if payment is enabled for this form
        if ( empty( $form_settings ) || empty( $form_settings['enable'] ) || $form_settings['enable'] == 'no' ) {
            return false;
        }
        return true;
    }

    static function store_post_data_by_session() {
        $_SESSION[self::$session_prefix] = $_POST;
    }

    static function get_post_data_by_session() {
        return $_SESSION[self::$session_prefix];
    }

    static function clear_session_transient() {
        unset($_SESSION[self::$session_prefix]);
    }

}
