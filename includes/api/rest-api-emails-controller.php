<?php

namespace CF7PA_Pay_Addons\API;

if (!defined('ABSPATH')) {
  exit;
}

use CF7PA_Pay_Addons\Shared\Security_Utils;
use CF7PA_Pay_Addons\Shared\Logger;
use CF7PA_Pay_Addons\Email\Mailer;
use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Rest_API_Emails_Controller extends \WP_REST_Controller
{
  public function __construct()
  {
    $this->namespace = CF7PA_ADDONS_REST_API . 'admin';
    $this->rest_base = 'emails';
  }

  /**
   * Registers the routes for the objects of the controller.
   *
   * @since 4.7.0
   *
   * @see register_rest_route()
   */
  public function register_routes()
  {
    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base,
      array(
        array(
          'methods'              => WP_REST_Server::READABLE,
          'callback'            => array($this, 'get_emails'),
          'permission_callback' => array($this, 'verify_admin')
        )
      )
    );

    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/test',
      array(
        array(
          'methods'             => WP_REST_Server::READABLE,
          'callback'            => array($this, 'send_test_email'),
          'permission_callback' => array($this, 'verify_admin'),
        ),
      )
    );

    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/setting',
      array(
        array(
          'methods'              => WP_REST_Server::READABLE,
          'callback'            => array($this, 'get_email_setting'),
          'permission_callback' => array($this, 'verify_admin')
        ),
        array(
          'methods'             => WP_REST_Server::EDITABLE,
          'callback'            => array($this, 'update_email_setting'),
          'permission_callback' => array($this, 'verify_admin'),
        ),
      )
    );

    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/(?P<id>.+)',
      array(
        array(
          'methods'              => WP_REST_Server::READABLE,
          'callback'            => array($this, 'get_email_by_id'),
          'permission_callback' => array($this, 'verify_admin'),
        ),
        array(
          'methods'             => WP_REST_Server::EDITABLE,
          'callback'            => array($this, 'update_email_by_id'),
          'permission_callback' => array($this, 'verify_admin'),
        )
      )
    );
  }

  function verify_admin(WP_REST_Request $request)
  {
    return Security_Utils::admin_check($request);
  }

  public function get_emails()
  {
    $emails = get_option('cf7pa_email_list');
    $data = array(
      'pageSize' => count($emails),
      'total' => count($emails),
      'list' => $emails,
    );
    return new WP_REST_Response($data);
  }

  public function get_email_by_id($request)
  {
    $emailId = sanitize_text_field($request['id']);
    $email = Mailer::get_email_by_id($emailId);
    if (!empty($email)) {
      return new WP_REST_Response($email);
    }
    Logger::error('get email failed', $emailId);
    return new WP_Error('rest_parameter', __('get email failed.'), array('status' => 400));;
  }

  public function update_email_by_id($request)
  {
    if ( ! current_user_can( 'edit_themes' ) && Mailer::is_custom_template_enabled()) {
      $error = 'You don&#8217;t have permission to do this.';
      Logger::error('update email setting failed', $error);
      return new WP_Error('rest_parameter', __($error), array('status' => 403));;
    }
    $emailId = sanitize_text_field($request['id']);
    $postData = sanitize_post($request->get_params());
    if(Mailer::update_email_by_id($emailId, $postData)) {
      return new WP_REST_Response([]);
    }
    
    return new WP_Error('rest_parameter', __('update email failed.'), array('status' => 400));;
  }

  public function get_email_setting($request)
  {
    $setting = Mailer::get_email_setting();
    return new WP_REST_Response($setting);
  }

  public function update_email_setting($request)
  {
    if (
      !isset($request['fromName']) || !isset($request['fromEmail'])
    ) {
      return new WP_Error('rest_parameter', __('parameter missing.'), array('status' => 400));
    }


    $postData = sanitize_post($request->get_params());
    if(Mailer::update_email_setting($postData)) {
      return new WP_REST_Response([]);
    }
    
    Logger::error('update email setting failed', $postData);
    return new WP_REST_Response(true);
  }

  public function send_test_email($request) {
    $emailId = sanitize_text_field($request['id']);
    // $emailTo = sanitize_text_field($request['to']) ?? get_option('stripe_express_email_sender_from_email');
    $emailTo = get_option('stripe_express_email_sender_from_email');
    $result = Mailer::send_test_email($emailId, $emailTo);
    return new WP_REST_Response($result);
  }
}
