<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

// Are we white-labeled?
$rwl = rocket_is_white_label();

add_settings_section( 'rocket_display_imp_options', __( 'Advanced options', 'rocket' ), '__return_false', 'rocket_advanced' );

/**
 * Panel caption
 */
if ( ! $rwl ) {

	add_settings_field(
		'rocket_advanced_options_panel',
		false,
		'rocket_field',
		'rocket_advanced',
		'rocket_display_imp_options',
		array(
			array(
				'type'         => 'helper_panel_description',
				'name'         => 'advanced_options_panel_caption',
				'description'  => sprintf(
					'<span class="dashicons dashicons-admin-tools" aria-hidden="true"></span><strong>%1$s</strong>',
					/* translators: line break recommended, but not mandatory; use URL of localised document if available in your language  */
					__( 'These settings are for advanced cache management. Caching itself works automatically.<br>Read the <a href="http://docs.wp-rocket.me/article/78-how-often-is-the-cache-updated" target="_blank">documentation on automatic cache management</a> to better understand how caching works.', 'rocket' )
				),
			),
		)
	);
}

/**
 * Never cache (URLs)
 */
$rocket_reject_uri = array();

$rocket_reject_uri[] = array(
	'type'         => 'helper_help',
	'name'         => 'reject_uri',
	'description'  => __( 'Specify URLs of pages or posts that should never get cached (one per line)', 'rocket' ),
);
$rocket_reject_uri[] = array(
	'type'         => 'textarea',
	'label_for'    => 'cache_reject_uri',
	'label_screen' => __( 'Never cache (URLs):', 'rocket' ),
	'placeholder'  => '/members/(.*)',
);

$ecommerce_plugin_name = '';

if ( function_exists( 'WC' ) && function_exists( 'wc_get_page_id' ) ) {

	$ecommerce_plugin_name = _x( 'WooCommerce', 'plugin name', 'rocket' );

} elseif ( function_exists( 'EDD' ) ) {

	$ecommerce_plugin_name = _x( 'Easy Digital Downloads', 'plugin name', 'rocket' );

} elseif ( function_exists( 'it_exchange_get_page_type' ) && function_exists( 'it_exchange_get_page_url' ) ) {

	$ecommerce_plugin_name = _x( 'iThemes Exchange', 'plugin name', 'rocket' );

} elseif ( defined( 'JIGOSHOP_VERSION' ) && function_exists( 'jigoshop_get_page_id' ) ) {

	$ecommerce_plugin_name = _x( 'Jigoshop', 'plugin name', 'rocket' );

} elseif ( defined( 'WPSHOP_VERSION' ) && class_exists( 'wpshop_tools' ) && method_exists( 'wpshop_tools','get_page_id' ) ) {

	$ecommerce_plugin_name = _x( 'WP-Shop', 'plugin name', 'rocket' );
}

if ( ! empty( $ecommerce_plugin_name ) ) {

	$rocket_reject_uri[] = array(
		'type'         => 'helper_detection',
		'description'  => sprintf(
			/* translators: %s = plugin name, e.g. WooCommerce */
			__( 'Cart and checkout pages set in <strong>%s</strong> will be detected and never cached by default. No need to enter them here.', 'rocket' ),
			$ecommerce_plugin_name
		),
	);
}

$rocket_reject_uri[] = array(
	'type'         => 'helper_description',
	'description'  => sprintf(
		/* translators: line-break recommended; %s = code sample  */
		__( 'The domain part of the URL will be stripped automatically.<br>Use %s wildcards to address multiple URLs under a given path.', 'rocket' ),
		'<code>(.*)</code>'
	),
);

add_settings_field(
	'rocket_reject_uri',
	__( 'Never cache (URLs):', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	$rocket_reject_uri
);

/**
 * Never cache (cookies)
 */
add_settings_field(
	'rocket_reject_cookies',
	__( 'Never cache (cookies):', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'helper_help',
			'name'         => 'reject_cookies',
			'description'  => __( 'Specify the IDs of cookies that, when set in the visitor’s browser, should prevent a page from getting cached (one per line)', 'rocket' ),
		),
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_reject_cookies',
			'label_screen' => __( 'Never cache pages when these cookies are present:', 'rocket' ),
		),
	)
);

/**
 * Never cache (user agents)
 */
add_settings_field(
	'rocket_reject_ua',
	__( 'Never cache (user agents):', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'helper_help',
			'name'         => 'cache_reject_ua',
			'description'  => __( 'Specify user agent strings that should never see cached pages (one per line)', 'rocket' ),
		),
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_reject_ua',
			'label_screen' => __( 'Never send cache pages for these user agents:', 'rocket' ),
			'placeholder'  => '(.*)Mobile(.*)Safari(.*)',
		),
		array(
			'type'         => 'helper_description',
			'description'  => sprintf(
				/* translators: %1$s = (.*), %2$s = URL  */
				__( 'Use %1$s wildcards to <a href="%2$s" target="_blank">detect parts of UA strings</a>.', 'rocket' ),
				'<code>(.*)</code>',
				'https://developer.mozilla.org/en-US/docs/Web/HTTP/Browser_detection_using_the_user_agent'
			),
		),
	)
);

/**
 * Always purge (URLs)
 */
add_settings_field(
	'rocket_purge_pages',
	__( 'Always purge (URLs):', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'helper_help',
			'name'         => 'purge_pages',
			'description'  => __( 'Specify URLs you always want purged from cache whenever you update any post or page (one per line)', 'rocket' ),
		),
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_purge_pages',
			'label_screen' => __( 'Always purge these URLs from cache when updating any post or page:', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'description'  => sprintf(
				/* translators: line-break recommended; %s = code sample  */
				__( 'The domain part of the URL will be stripped automatically.<br>Use %s wildcards to address multiple URLs under a given path.', 'rocket' ),
				'<code>(.*)</code>'
			),
		),
	)
);

/**
 * Cache query strings
 */
add_settings_field(
	'rocket_query_strings',
	__( 'Cache query strings:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'helper_help',
			'name'         => 'query_strings',
			'description'  => __( 'Specify query strings for caching (one per line)', 'rocket' ),
		),
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_query_strings',
			'label_screen' => __( 'Force caching for URLs with these query strings (GET parameters):', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'query_strings',
			'description'  => $rwl ? __( 'Cache for query strings enables you to force caching for specific GET parameters.', 'rocket' ) : __( '<a href="http://docs.wp-rocket.me/article/971-caching-query-strings" target="_blank">Cache for query strings</a> enables you to force caching for specific GET parameters.', 'rocket' ),
		),
	)
);
