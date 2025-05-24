<?php

namespace CF7PA_Pay_Addons;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}
use CF7PA_Pay_Addons\Traits\Admin;
use CF7PA_Pay_Addons\Stripe\Stripe_Settings;
use CF7PA_Pay_Addons\API\Rest_API_Stripe_Checkout_Controller;
use CF7PA_Pay_Addons\API\Rest_API_Stripe_Webhooks_Controller;
use CF7PA_Pay_Addons\API\Rest_API_Settings_Controller;
use CF7PA_Pay_Addons\API\Rest_API_Emails_Controller;
final class Bootstrap {
    use Admin;
    // instance container
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        if ( $this->is_compatible() ) {
            // register hooks
            $this->register_hooks();
        }
    }

    public function is_compatible() {
        if ( !function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
            add_action( 'admin_notices', [$this, 'admin_notice_missing_main_plugin'] );
            return false;
        }
        return true;
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have Elementor installed or activated.
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_missing_main_plugin() {
        $message = sprintf( 
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'cf7-pay-addons' ),
            '<strong>' . CF7PA_PLUGIN_NAME . '</strong>',
            '<strong>' . esc_html__( 'Contact Form 7', 'cf7-pay-addons' ) . '</strong>'
         );
        // printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
        printf( esc_html__( $message ) );
    }

    protected function register_hooks() {
        add_filter(
            'plugin_action_links_' . plugin_basename( CF7PA_ADDONS_FILE ),
            [$this, 'add_plugin_action_link'],
            10,
            5
        );
        // rest api
        add_action( 'rest_api_init', array($this, 'register_apis') );
        // admin section
        if ( is_admin() ) {
            // Admin
            add_action( 'admin_menu', array($this, 'admin_menu') );
            add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );
            // cf7 setting
            $this->init_contact_form_checkout_setting();
        }
        // Frontend CSS
        // make sure before contact form 7
        add_action(
            'wp_enqueue_scripts',
            [$this, 'enqueue_scripts'],
            9,
            0
        );
    }

    public function add_plugin_action_link( $actions ) {
        $mylinks = array('<a href="' . admin_url( 'admin.php?page=contact-form-7-pay-addons' ) . '">Settings</a>', '<a href="https://cf7-docs.payaddons.com" target="_blank">Documentation</a>');
        $actions = array_merge( $mylinks, $actions );
        return $actions;
    }

    public function register_apis() {
        $settings_api = new Rest_API_Settings_Controller();
        $email_api = new Rest_API_Emails_Controller();
        $stripe_api = new Rest_API_Stripe_Checkout_Controller();
        $webhooks_api = new Rest_API_Stripe_Webhooks_Controller();
        $settings_api->register_routes();
        $email_api->register_routes();
        $webhooks_api->register_routes();
        $stripe_api->register_routes();
    }

    public function enqueue_scripts() {
        wp_register_script(
            'stripe',
            'https://js.stripe.com/v3/',
            [],
            '3.0'
        );
        wp_register_script(
            'cf7-pay-addons',
            CF7PA_ADDONS_ASSET_URL . 'frontend/js/pay-addons.js',
            ['jquery'],
            CF7PA_PLUGIN_VERSION
        );
        wp_register_style(
            'cf7-pay-addons',
            CF7PA_ADDONS_ASSET_URL . 'frontend/css/pay-addons.css',
            [],
            CF7PA_PLUGIN_VERSION
        );
        wp_enqueue_script( 'stripe' );
        wp_enqueue_script( 'cf7-pay-addons' );
        wp_enqueue_style( 'cf7-pay-addons' );
        wp_localize_script( 'cf7-pay-addons', 'cf7paSettings', array(
            'root'   => esc_url_raw( rest_url() . CF7PA_ADDONS_REST_API ),
            'nonce'  => wp_create_nonce( 'wp_rest' ),
            'apiKey' => Stripe_Settings::get_publishable_key(),
        ) );
    }

}
