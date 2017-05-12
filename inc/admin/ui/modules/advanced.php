<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

add_settings_section( 'rocket_display_imp_options', __( 'Advanced options', 'rocket' ), '__return_false', 'rocket_advanced' );

add_settings_field(
	'rocket_purge_pages',
	__( 'Empty the cache of the following pages when updating a post:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_purge_pages',
			'label_screen' => __( 'Empty the cache of the following pages when updating a post:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'purge_pages',
			'description'  => __( 'Enter the URL of additional pages to purge when updating a post (one per line).', 'rocket' ) . '<br/>' .
								  __( 'You can use regular expressions (regex).', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'purge_pages',
			'description'  => __( '<strong>Note:</strong> When you update a post or when a comment is posted, the homepage, categories and tags associated with this post are automatically removed from the cache and then recreated by our bot.', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_reject_uri',
	__( 'Never cache the following pages:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_reject_uri',
			'label_screen' => __( 'Never cache the following pages:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'reject_uri',
			'description'  => __( 'Enter the URL of pages to reject (one per line).', 'rocket' ) . '<br/>' . __( 'You can use regular expressions (regex).', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'cache_reject_ua',
			'description'  => __( '<strong>Note:</strong> The cart and checkout pages are auto-excluded from the cache for WooCommerce, Easy Digital Download, iThemes Exchange, Jigoshop & WP-Shop.', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_reject_cookies',
	__( 'Don\'t cache pages that use the following cookies:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_reject_cookies',
			'label_screen' => __( 'Don\'t cache pages that use the following cookies:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'reject_cookies',
			'description'  => __( 'List the names of the cookies (one per line).', 'rocket' ),
			),
	)
);
add_settings_field(
	'rocket_query_strings',
	__( 'Cache pages that use the following query strings (GET parameters):', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_query_strings',
			'label_screen' => __( 'Cache pages that use the following query strings (GET parameters):', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'query_strings',
			'description'  => __( 'List of query strings which can be cached (one per line).', 'rocket' ),
			),
	)
);
add_settings_field(
	'rocket_reject_ua',
	__( 'Never send cache pages for these user agents:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_reject_ua',
			'label_screen' => __( 'Never send cache pages for these user agents:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'cache_reject_ua',
			'description'  => __( 'Enter the user agents name to reject (one per line).', 'rocket' ) . '<br/>' . __( 'You can use regular expressions (regex).', 'rocket' ),
		),
	)
);
