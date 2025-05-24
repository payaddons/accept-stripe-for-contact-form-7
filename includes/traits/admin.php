<?php

namespace CF7PA_Pay_Addons\Traits;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
trait Admin
{
    /**
     * Create an admin menu.
     *
     * @since 1.0.0
     */
    public function admin_menu() {
        $svg_img = 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMjQiIGhlaWdodD0iMTAyNCIgdmlld0JveD0iMCAwIDEwMjQgMTAyNCI+CjxnIGlkPSJpY29tb29uLWlnbm9yZSI+CjwvZz4KPHBhdGggZmlsbD0iI2EyYWFiMiIgZD0iTTgyNC44ODcgODUuMzMzYzYyLjgzOSAwIDExMy43NzkgNTAuOTQgMTEzLjc3OSAxMTMuNzc4djYyNS43NzZjMCA2Mi44MzktNTAuOTQgMTEzLjc3OS0xMTMuNzc5IDExMy43NzloLTYyNS43NzZjLTYyLjgzOCAwLTExMy43NzgtNTAuOTQtMTEzLjc3OC0xMTMuNzc5di02MjUuNzc2YzAtNjIuODM4IDUwLjk0LTExMy43NzggMTEzLjc3OC0xMTMuNzc4aDYyNS43NzZ6TTQxMS43NzkgNzg2Ljk2MXYtMjExLjIwOWgxMTcuMjdjMzcuMTUgMCA3MC40MTctNy4yNzkgOTkuODA2LTIxLjg1IDI5Ljk0My0xNC41NjYgNTMuNTA4LTM1LjA2MyA3MC42OTQtNjEuNSAxNy4xOS0yNi45NzQgMjUuNzgzLTU4LjgwMyAyNS43ODMtOTUuNDg5IDAtMzYuNjg1LTguNTkzLTY4LjI0NS0yNS43ODMtOTQuNjgtMTcuMTg2LTI2Ljk3NC00MC43NTEtNDcuNzQ0LTcwLjY5NC02Mi4zMTEtMjkuMzg5LTE0LjU2Ni02Mi42NTYtMjEuODQ5LTk5LjgwNi0yMS44NDloLTIzMC4zODN2NTY4Ljg4N2gxMTMuMTEzek01MTcuNDA2IDQ4NS45MzFoLTEwNS42MjZ2LTE3OC4wMzJoMTA1LjYyNmMxNy43NDUgMCAzMy44MjIgMy41MDcgNDguMjM5IDEwLjUyczI1Ljc4MyAxNy4yNjQgMzQuMTAzIDMwLjc1MWM4LjMxMSAxMy40ODcgMTIuNDcxIDI5LjQwMiAxMi40NzEgNDcuNzQ0IDAgMTguODgzLTQuMTYgMzUuMDY1LTEyLjQ3MSA0OC41NTYtOC4zMiAxMi45NDUtMTkuNjg2IDIyLjkyNS0zNC4xMDMgMjkuOTM5cy0zMC40OTQgMTAuNTIyLTQ4LjIzOSAxMC41MjJ6TTYwNy4yNDkgNzgxLjMwOGMxMi45MzIgMTMuMjQ4IDI5LjUyMSAxOS44NzggNDkuNzY2IDE5Ljg3OCAxOS42NzggMCAzNS45ODUtNi42MyA0OC45MTctMTkuODc4IDEyLjkzMi0xMy4yNTIgMTkuNDAxLTI5LjU0MiAxOS40MDEtNDguODYyIDAtMTkuMzI0LTYuNDY4LTM1LjYxNC0xOS40MDEtNDguODY2LTEyLjkzMi0xMy4yNDgtMjkuMjM5LTE5Ljg3OC00OC45MTctMTkuODc4LTIwLjI0NSAwLTM2LjgzNCA2LjYzLTQ5Ljc2NiAxOS44NzgtMTIuOTMyIDEzLjI1Mi0xOS4zOTYgMjkuNTQyLTE5LjM5NiA0OC44NjYgMCAxOS4zMTkgNi40NjQgMzUuNjEgMTkuMzk2IDQ4Ljg2MnoiPjwvcGF0aD4KPC9zdmc+Cg==';
        add_menu_page(
            __( 'Contact Pay', 'contact-form-7-stripe-addon' ),
            __( 'Contact Pay', 'contact-form-7-stripe-addon' ),
            'manage_options',
            'contact-form-7-pay-addons',
            array($this, 'admin_settings_page'),
            $svg_img,
            30
        );
    }

    /**
     * Loading all scripts
     *
     * @since 1.0.0
     */
    public function admin_enqueue_scripts( $hook ) {
        $support_pages = ['contact-form-7-pay-addons', 'wpcf7-new', 'wpcf7'];
        if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $support_pages ) ) {
            wp_enqueue_script(
                'epa-tiptip',
                CF7PA_ADDONS_ASSET_URL . 'lib/js/jquery-tiptip/jquery.tipTip.js',
                array('jquery'),
                CF7PA_PLUGIN_VERSION
            );
            wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css' );
            wp_enqueue_script(
                'select2',
                'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
                array('jquery'),
                '4.0.13',
                true
            );
            // wp_enqueue_style('flowbite-css', 'https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.0/flowbite.min.css', false, null, false );
            wp_enqueue_script(
                'flowbite-js',
                'https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.0/flowbite.min.js',
                false,
                null,
                true
            );
            wp_enqueue_style(
                'cf7-pay-addons-admin-css',
                CF7PA_ADDONS_ASSET_URL . 'admin/css/admin.css',
                false,
                CF7PA_PLUGIN_VERSION
            );
            wp_enqueue_script(
                'cf7-pay-addons-admin-js',
                CF7PA_ADDONS_ASSET_URL . 'admin/js/admin.js',
                false,
                CF7PA_PLUGIN_VERSION
            );
            wp_localize_script( 'cf7-pay-addons-admin-js', 'cf7paSettings', array(
                'root'   => esc_url_raw( rest_url() . CF7PA_ADDONS_REST_API ),
                'nonce'  => wp_create_nonce( 'wp_rest' ),
                'locale' => get_locale(),
            ) );
        }
    }

    public function init_contact_form_checkout_setting() {
        new \CF7PA_Pay_Addons\Admin\CF7\Checkout_Redirect_Setting();
    }

    /**
     * Create settings page.
     *
     * @since 1.0.0
     */
    public function admin_settings_page() {
        require_once CF7PA_ADDONS_PATH . '/includes/admin/templates/index.php';
    }

}