<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

add_settings_section( 'rocket_display_cloudflare_options', 'Cloudflare', '__return_false', 'rocket_cloudflare' );

/**
 * CF email
 */
add_settings_field(
	'rocket_cloudflare_email',
	_x( 'Account email:', 'Cloudflare', 'rocket' ),
	'rocket_field',
	'rocket_cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'helper_help',
			'description'  => __( 'Enter the email address of your Cloudflare account', 'rocket' ),
		),
		array(
			'type'         => 'text',
			'label_for'    => 'cloudflare_email',
			'label_screen' => __( 'Cloudflare account email address', 'rocket' ),
		),
	)
);

/**
 * CF API key
 */
if ( ! defined( 'WP_ROCKET_CF_API_KEY_HIDDEN' ) || ! WP_ROCKET_CF_API_KEY_HIDDEN ) {

	$rocket_cloudflare_api_key = array();

	$rocket_cloudflare_api_key[] = array(
		'type'        => 'helper_help',
		'description' => __( 'Enter the global API key of your Cloudflare account', 'rocket' ),
	);

	$rocket_cloudflare_api_key[] = array(
		'type'         => 'cloudflare_api_key',
		'label_for'    => 'cloudflare_api_key',
		'label_screen' => __( 'Global API key', 'rocket' ),
	);

	$cf_api_key = get_rocket_option( 'cloudflare_api_key' );
	if ( empty( $cf_api_key ) ) {
		$rocket_cloudflare_api_key[] = array(
			'type'         => 'helper_description',
			// translators: %s is the URL to the CloudFlare documentation.
			'description'  => sprintf( __( '<a href="%s" target="_blank">Retrieve your API key</a>', 'rocket' ), 'https://support.cloudflare.com/hc/en-us/articles/200168246' ),
		);
	}


	add_settings_field(
		'rocket_cloudflare_api_key',
		_x( 'Global API key:', 'Cloudflare', 'rocket' ),
		'rocket_field',
		'rocket_cloudflare',
		'rocket_display_cloudflare_options',
		$rocket_cloudflare_api_key
	);
}

/**
 * CF domain
 */
$cf_readonly = '';

if ( function_exists( 'rocket_cloudflare_valid_auth' ) ) {
	$cf_readonly   = ( is_wp_error( rocket_cloudflare_valid_auth() ) ) ? 'readonly' : '';
}

add_settings_field(
	'rocket_cloudflare_domain',
	_x( 'Domain:', 'Cloudflare', 'rocket' ),
	'rocket_field',
	'rocket_cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'text',
			'label_for'    => 'cloudflare_domain',
			'label_screen' => __( 'Cloudflare domain', 'rocket' ),
			'readonly'     => $cf_readonly,
			'default'      => rocket_get_domain( home_url() ),
		),
	)
);

/**
 * CF dev mode
 */
add_settings_field(
	'rocket_cloudflare_devmode',
	_x( 'Development mode:', 'Cloudflare', 'rocket' ),
	'rocket_field',
	'rocket_cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'select',
			'label_for'    => 'cloudflare_devmode',
			'label_screen' => __( 'Development Mode', 'rocket' ),
			'options'      => array(
				0 => __( 'Off', 'rocket' ),
				1 => __( 'On', 'rocket' ),
			),
			'readonly'     => $cf_readonly,
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'cloudflare_devmode',
			// translators: %s is the URL to the CloudFlare documentation.
			'description'  => sprintf( __( 'Temporarily enter development mode on your website. This setting will automatically get turned off after 3 hours. <a href="%s" target="_blank">Learn more</a>', 'rocket' ), 'https://support.cloudflare.com/hc/en-us/articles/200168246' ),
		),
	)
);

/**
 * CF optimal settings
 */
add_settings_field(
	'rocket_cloudflare_auto_settings',
	_x( 'Optimal settings:', 'Cloudflare', 'rocket' ),
	'rocket_field',
	'rocket_cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'select',
			'label_for'    => 'cloudflare_auto_settings',
			'label_screen' => _x( 'Optimal settings:', 'Cloudflare', 'rocket' ),
			'options'      => array(
				0 => __( 'Off', 'rocket' ),
				1 => __( 'On', 'rocket' ),
			),
			'readonly'     => $cf_readonly,
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'cloudflare_auto_settings',
			'description'  => __( 'Automatically enhances your Cloudflare configuration for speed, performance grade, and compatibility.', 'rocket' ),
		),
	)
);

/**
 * CF relative protocol
 */
add_settings_field(
	'rocket_cloudflare_protocol_rewrite',
	_x( 'Relative Protocol:', 'Cloudflare', 'rocket' ),
	'rocket_field',
	'rocket_cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'select',
			'label_for'    => 'cloudflare_protocol_rewrite',
			'label_screen' => __( 'HTTPS Protocol Rewrite', 'rocket' ),
			'options'      => array(
				0 => __( 'Off', 'rocket' ),
				1 => __( 'On', 'rocket' ),
			),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'cloudflare_protocol_rewrite',
			'description'  => sprintf(
				// translators: %s is the URL to the CloudFlare documentation.
				__( 'Should only be used with Cloudflare’s <a href="%1$s" target="_blank">Flexible SSL</a> feature.<br>URLs of static files (CSS, JS, images) will be rewritten to use relative protocol (%2$s instead of %3$s or %4$s).', 'rocket' ),
				'https://support.cloudflare.com/hc/en-us/articles/200170416-What-do-the-SSL-options-Off-Flexible-SSL-Full-SSL-Full-SSL-Strict-mean-',
				'<code>//</code>',
				'<code>http://</code>',
				'<code>https://</code>'
			),
		),
	)
);

/**
 * CF clear cache
 */
add_settings_field(
	'rocket_purge_cloudflare',
	__( 'Clear Cloudflare cache:', 'rocket' ),
	'rocket_button',
	'rocket_cloudflare',
	'rocket_display_cloudflare_options',
	array(
		'button' => array(
			'button_label' => __( 'Clear Cloudflare cache', 'rocket' ),
			'url'          => wp_nonce_url( admin_url( 'admin-post.php?action=rocket_purge_cloudflare' ), 'rocket_purge_cloudflare' ),
		),
		'helper_description' => array(
			'name'         => 'purge_cloudflare',
			// translators: %s is the URL to the CloudFlare documentation.
			'description'  => sprintf( __( 'Purges cached resources for your website. <a href="%s" target="_blank">Learn more</a>', 'rocket' ), 'https://support.cloudflare.com/hc/en-us/articles/200169246' ),
		),
	)
);
