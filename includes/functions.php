<?php

function cf7pa_sanitize_tooltip($var)
{
	return htmlspecialchars(
		wp_kses(
			html_entity_decode($var ?? ''),
			array(
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'small'  => array(),
				'span'   => array(),
				'ul'     => array(),
				'li'     => array(),
				'ol'     => array(),
				'p'      => array(),
			)
		)
	);
}

function cf7pa_help_tip($tip, $allow_html = false)
{
	if ($allow_html) {
		$sanitized_tip = cf7pa_sanitize_tooltip($tip);
	} else {
		$sanitized_tip = esc_attr($tip);
	}

	/**
	 * Filter the help tip.
	 *
	 * @since 7.7.0
	 *
	 * @param string $tip_html       Help tip HTML.
	 * @param string $sanitized_tip  Sanitized help tip text.
	 * @param string $tip            Original help tip text.
	 * @param bool   $allow_html     Allow sanitized HTML if true or escape.
	 *
	 * @return string
	 */
	return apply_filters('cf7pa_help_tip', '<span class="cf7pa-help-tip" tabindex="0" aria-label="' . $sanitized_tip . '" data-tip="' . $sanitized_tip . '"></span>', $sanitized_tip, $tip, $allow_html);
}

function cf7pa_upgrade_link()
{
	echo '<section class="flex justify-center">
	<a class="font-medium text-blue-600 dark:text-blue-500 hover:underline" href="' . esc_url(cf7pa_fs()->get_upgrade_url()) . '">' . esc_html__('Upgrade to unlock this feature!', 'contact-form-7-stripe-addon') . '</a>
	</section>';
}

function cf7pa_get_form_setting($name)
{
	$current_form = WPCF7_ContactForm::get_current();
	if ($current_form) {
		return get_post_meta($current_form->id(), $name, true);
	}
	return null;
}

function cf7pa_hidden_field($hidden_fields)
{
	$checkout_settings = cf7pa_get_form_setting('cf7pa_checkout_form_setting');
	$redirect_settings = cf7pa_get_form_setting('cf7pa_checkout_redirect_setting');
	if (!empty($checkout_settings) && $checkout_settings['enable'] === 'yes') {
		$hidden_fields['_cf7pa_payment_intent_id'] = '';
		$hidden_fields['_cf7pa_form_checkout'] = true;
		$hidden_fields['_cf7pa_version'] = CF7PA_PLUGIN_VERSION;
	}
	if (!empty($redirect_settings) && $redirect_settings['enable'] === 'yes') {
		$hidden_fields['_cf7pa_session_checkout_id'] = '';
		$hidden_fields['_cf7pa_form_redirect'] = true;
		$hidden_fields['_cf7pa_version'] = CF7PA_PLUGIN_VERSION;
	}
	return $hidden_fields;
}
add_filter('wpcf7_form_hidden_fields', 'cf7pa_hidden_field');

// skip spam if checkout form
function cf7pa_skip_spam_filter ($skip, $submission) {
	$checkout_settings = cf7pa_get_form_setting('cf7pa_checkout_form_setting');
	if (!empty($checkout_settings) && $checkout_settings['enable'] === 'yes') {
		return true;
	}
	return $skip;
};
add_filter('wpcf7_skip_spam_check', 'cf7pa_skip_spam_filter', 10, 2);

// Start session if not already started
add_action('init', function() {
	if (!session_id()) {
			// session_start(); // site health fix
	}
});

function cf7pa_add_custom_mail_tags($output, $name, $html, $mail_tag) {
    if ($name === 'payment_intent_id') {
        return isset($_POST['_cf7pa_payment_intent_id']) 
            ? $_POST['_cf7pa_payment_intent_id'] 
            : '';
    }

    if ($name === 'session_checkout_id') {
        return isset($_POST['_cf7pa_session_checkout_id']) 
            ? $_POST['_cf7pa_session_checkout_id'] 
            : '';
    }

    return $output;
}
add_filter('wpcf7_special_mail_tags', 'cf7pa_add_custom_mail_tags', 10, 4);
