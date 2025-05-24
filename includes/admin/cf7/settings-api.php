<?php
/**
 * Abstract Settings API Class
 *
 * Admin Settings API used by Integrations, Shipping Methods, and Payment Gateways.
 *
 */
namespace CF7PA_Pay_Addons\Admin\CF7;

defined( 'ABSPATH' ) || exit;

/**
 * Settings_API class.
 */
abstract class Settings_API {
  
  static $_prefix = 'cf7pacr';

	// instance container
	private static $_instance = null;

	public static function instance()
	{

		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_text_field_value($setting, $key, $default_value)
	{
		return sanitize_text_field(isset($setting[$key]) ? $setting[$key] : $default_value);
	}

	public function get_field_key($key)
	{
		$prefix = static::$_prefix;
		return "{$prefix}[{$key}]";
	}

	public function get_tooltip_html($data)
	{
		if (true === $data['desc_tip']) {
			$tip = $data['description'];
		} elseif (! empty($data['desc_tip'])) {
			$tip = $data['desc_tip'];
		} else {
			$tip = '';
		}

		return $tip ? cf7pa_help_tip($tip, true) : '';
	}

	public function get_custom_attribute_html($data)
	{
		$custom_attributes = array();

		if (! empty($data['custom_attributes']) && is_array($data['custom_attributes'])) {
			foreach ($data['custom_attributes'] as $attribute => $attribute_value) {
				$custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
			}
		}

		return implode(' ', $custom_attributes);
	}

	public function generate_checkbox_field($key, $data)
	{
		$field_key = $this->get_field_key($key);
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args($data, $defaults);

		ob_start();
?>
		<div class="flex items-center justify-between">
			<label for="<?php echo esc_attr($field_key); ?>" class="mr-4"><?php echo wp_kses_post($data['label']); ?> <?php echo $this->get_tooltip_html($data); // WPCS: XSS ok. 
																																																										?></label>
			<input
				type="checkbox"
				id="<?php echo esc_attr($field_key); ?>"
				name="<?php echo esc_attr($field_key); ?>"
				value="yes" <?php checked($data['value'], 'yes'); ?>
				class="form-checkbox h-5 w-5 text-blue-600"
				<?php echo $this->get_custom_attribute_html($data); // WPCS: XSS ok. 
				?>>
		</div>
	<?php
		return ob_get_clean();
	}

	public function generate_text_field($key, $data)
	{
		$field_key = $this->get_field_key($key);
		$data = wp_parse_args($data, [
			'label' => '',
			'value' => '',
			'desc_tip' => false,
			'description' => '',
			'placeholder' => '',
			'custom_attributes' => array(),
		]);

		ob_start();
	?>
		<div>
			<label for="<?php echo esc_attr($field_key); ?>" class="block mb-2">
				<?php echo wp_kses_post($data['label']); ?>
				<?php echo $this->get_tooltip_html($data); ?>
			</label>
			<input type="text" id="<?php echo esc_attr($field_key); ?>"
				name="<?php echo esc_attr($field_key); ?>"
				value="<?php echo esc_attr($data['value']); ?>"
				placeholder="<?php echo esc_attr($data['placeholder']); ?>"
				<?php echo $this->get_custom_attribute_html($data); // WPCS: XSS ok. 
				?>
				class="form-input mt-1 block w-full">
			<span class="text-sm text-gray-500 mt-1"><?php echo wp_kses_post($data['description']); ?></span>
		</div>
	<?php
		return ob_get_clean();
	}

	public function generate_dropdown_field($key, $data)
	{
		$field_key = $this->get_field_key($key);
		$data = wp_parse_args($data, [
			'label' => '',
			'value' => '',
			'options' => [],
			'desc_tip' => false,
			'description' => '',
		]);

		ob_start();
	?>
		<div>
			<label for="<?php echo esc_attr($field_key); ?>" class="block mb-2">
				<?php echo wp_kses_post($data['label']); ?>
				<?php echo $this->get_tooltip_html($data); ?>
			</label>
			<select id="<?php echo esc_attr($field_key); ?>"
				name="<?php echo esc_attr($field_key); ?>"
				class="form-select mt-1 block w-full">
				<?php foreach ($data['options'] as $option_value => $option_label) : ?>
					<option value="<?php echo esc_attr($option_value); ?>"
						<?php selected($data['value'], $option_value); ?>>
						<?php echo esc_html($option_label); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php if(!$data['desc_tip']) echo '<span class="text-sm text-gray-500 mt-1">' . wp_kses_post($data['description']) . '</span>' ?>
		</div>
	<?php
		return ob_get_clean();
	}

	public function generate_multiselect_field($key, $data)
	{
		$field_key = $this->get_field_key($key);
		$data = wp_parse_args($data, [
			'label' => '',
			'value' => [],
			'options' => [],
			'desc_tip' => false,
			'description' => '',
		]);

		ob_start();
	?>
		<div>
			<label for="<?php echo esc_attr($field_key); ?>" class="block mb-2">
				<?php echo wp_kses_post($data['label']); ?>
				<?php echo $this->get_tooltip_html($data); ?>
			</label>
			<select id="<?php echo esc_attr($field_key); ?>"
				name="<?php echo esc_attr($field_key); ?>[]"
				class="form-multiselect mt-1 block w-full" multiple>
				<?php foreach ($data['options'] as $option_value => $option_label) : ?>
					<option value="<?php echo esc_attr($option_value); ?>"
						<?php selected(in_array($option_value, $data['value']), true); ?>>
						<?php echo esc_html($option_label); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	<?php
		return ob_get_clean();
	}

	public function render_form_fields($fields)
	{
		$is_premium = cf7pa_fs()->can_use_premium_code__premium_only();
		foreach ($fields as $key => $field) {
			$method = 'generate_' . $field['type'] . '_field';
			$allow = empty($field['premium']) || $is_premium;
			if (method_exists($this, $method) && $allow) {
				echo $this->$method($key, $field);
			}
		}
	}
}