<?php
/**
 * Plugin Name: goatcounter
 * Plugin URI:  https://www.goatcounter.com
 * Description: Wordpress integration with GoatCounter analytics.
 * Version:     0.1
 * License:     MIT
 */

// TODO: add options for:
// 1. no_onload, no_events, allow_local
// 2. callback for path/title/referrer
// 3. Ignore views from certain users (or all logged in users)
// 4. Ignore query parameters (rel=canonical isn't added by default)
// 5. noscript tracking
// 6. custom count.js location; maybe include it in this plugin?

// Insert the site code according to the settings.
add_action('wp_footer', 'goatcounter_code');
function goatcounter_code() {
	$opts = get_option('goatcounter_options');

	if ($opts['goatcounter_field_custom']) {
		print($opts['goatcounter_field_custom']);
		return;
	}

	printf('<script async data-goatcounter="%s" src="https://gc.zgo.at/count.js"></script>',
		esc_attr($opts['goatcounter_field_endpoint']));
}

// Add top level menu page.
add_action('admin_menu', 'goatcounter_options_page');
function goatcounter_options_page() {
	add_menu_page('GoatCounter', 'GoatCounter', 'manage_options',
		'goatcounter', 'goatcounter_options_page_html');
}

// Add settings page.
add_action('admin_init', 'goatcounter_settings');
function goatcounter_settings() {
	register_setting('goatcounter', 'goatcounter_options');

	add_settings_section('goatcounter_section_developers',
		__('Settings for GoatCounter analytics', 'goatcounter'),
		'goatcounter_section_developers',
		'goatcounter');

	add_settings_field('goatcounter_field_endpoint', __('Endpoint', 'goatcounter'),
		'goatcounter_field_endpoint', 'goatcounter',
		'goatcounter_section_developers', [
			'label_for'   => 'goatcounter_field_endpoint',
			'class'       => 'goatcounter_row',
            'description' => __('e.g. https://mycode.goatcounter.com/count', 'goatcounter'),
		]);

	add_settings_field('goatcounter_field_custom', __('Custom integration', 'goatcounter'),
		'goatcounter_field_custom', 'goatcounter',
		'goatcounter_section_developers', [
			'label_for'   => 'goatcounter_field_custom',
			'class'       => 'goatcounter_row',
            'description' => __('When filled in the above fields will be ignored, and this JS will be inserted as-is. See ‘site code’ in your GoatCounter menu for documentation. This should include the script tags etc.', 'goatcounter'),
		]);
}

// Generate settings page HTML.
function goatcounter_options_page_html() {
	if (!current_user_can('manage_options'))
		return;

	if (isset($_GET['settings-updated']))
		add_settings_error('goatcounter_messages', 'goatcounter_message', __('Settings Saved', 'goatcounter'), 'updated');

	settings_errors('goatcounter_messages');

	print('<div class="wrap">');
	printf('<h1>%s</h1>', esc_html(get_admin_page_title()));
	print('<form action="options.php" method="post">');

	settings_fields('goatcounter');
	do_settings_sections('goatcounter');
	submit_button('Save Settings');

	print('</form></div>');
}

function goatcounter_section_developers($args) {
	printf('<p id="%s">See <a href="https://www.goatcounter.com/contact">https://www.goatcounter.com/contact</a> on how to get support, report bugs, etc.</p>',
		esc_attr($args['id']));
}

function goatcounter_field_endpoint($args) {
	$opts = get_option('goatcounter_options');
	printf('<input type="text" id="%s" name="goatcounter_options[%1$s]" class="regular-text" value="%s">
		<p class="description" id="%1$s-description">%s</p>',
		esc_attr($args['label_for']),
		esc_attr($opts['goatcounter_field_endpoint']),
		esc_html($args['description']));
}

function goatcounter_field_custom($args) {
	$opts = get_option('goatcounter_options');
	printf('<textarea id="%s" name="goatcounter_options[%1$s]" class="regular-text">%s</textarea>
		<p class="description" id="%1$s-description">%s</p>',
		esc_attr($args['label_for']),
		esc_html($opts['goatcounter_field_custom']),
		esc_html($args['description']));
}
