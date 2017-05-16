<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

add_settings_section( 'rocket_display_main_options', __( 'Basic options', 'rocket' ), '__return_false', 'rocket_basic' );

/**
 * Panel caption
 */
add_settings_field(
	'rocket_basic_options_panel',
	false,
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	array(
		array(
			'type'         => 'helper_panel_description',
			'name'         => 'basic_options_panel_caption',
			'description'  => sprintf(
				'<span class="dashicons dashicons-performance" aria-hidden="true"></span><strong>%1$s</strong>',
				/* translators: line break is recommended, but not mandatory  */
				__( 'Caching has been activated automatically, your website should load fast!<br>How about <a href="https://wp-rocket.me/blog/correctly-measure-websites-page-load-time/" target="_blank">testing your loading time</a>? Maybe you don’t even need to configure all these options.', 'rocket' )
			),
		),
	)
);

/**
 * LazyLoad
 */

/* Dynamic warning */
$rocket_lazyload_fields = array();

$rocket_lazyload_fields[] = array(
	'type'        => 'helper_warning',
	'name'        => 'lazyload_common_issues',
	'description' => __( 'Deactivate in case you notice any visually broken items on your website. <a href="http://docs.wp-rocket.me/article/278-common-issues-with-lazyload" target="_blank">Why?</a>', 'rocket' ),
);

/* LazyLoad options */
$rocket_lazyload_fields[] =	array(
	'type'         => 'checkbox',
	'label'        => __( 'Enable for images', 'rocket' ),
	'label_for'    => 'lazyload',
	'label_screen' => __( 'Enable LazyLoad for images', 'rocket' ),
);
$rocket_lazyload_fields[] = array(
	'type'         => 'checkbox',
	'label'        => __( 'Enable for iframes and videos', 'rocket' ),
	'label_for'    => 'lazyload_iframes',
	'label_screen' => __( 'Enable LazyLoad for iframes and videos', 'rocket' ),
);
$rocket_lazyload_fields[] = array(
	'type'         => 'helper_performance',
	'name'         => 'lazyload_perf_tip',
	'description'  => __( 'Reduces the number of HTTP requests, can improve loading time.', 'rocket' )
);
$rocket_lazyload_fields[] = array(
	'type'         => 'helper_description',
	'name'         => 'lazyload',
	'description'  => __( 'Images, iframes, and videos will be loaded only as they enter (or are about to enter) the viewport.', 'rocket' )
);

add_settings_field(
	'rocket_lazyload',
	__( 'LazyLoad:', 'rocket' ),
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	$rocket_lazyload_fields
);

/**
 * Mobile cache
 */
add_settings_field(
	'rocket_mobile',
	__( 'Mobile cache:', 'rocket' ),
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Enable caching for mobile devices', 'rocket' ),
			'label_for'	   => 'cache_mobile',
			'label_screen' => __( 'Mobile cache:', 'rocket' ),
			'default'	   => ( rocket_is_mobile_plugin_active() ) ? 1 : get_rocket_option( 'cache_mobile', 0 ),
			'readonly'	   => rocket_is_mobile_plugin_active(),
		),
		array(
			'type'         => 'helper_performance',
			'name'         => 'mobile_perf_tip',
			'description'  => __( 'Makes your website mobile-friendlier.', 'rocket' ),
		),
		array(
			'parent'       => 'cache_mobile',
			'type'         => 'checkbox',
			'label'        => __( 'Separate cache files for mobile devices', 'rocket' ),
			'name'         => 'do_caching_mobile_files',
			'default'	   => ( rocket_is_mobile_plugin_active() ) ? 1 : get_rocket_option( 'do_caching_mobile_files', 0 ),
			'readonly'	   => rocket_is_mobile_plugin_active(),
		),
		array(
			'parent'       => 'cache_mobile',
			'type'         => 'helper_description',
			'name'         => 'mobile',
			'description'  => __( '<a href="http://docs.wp-rocket.me/article/708-mobile-caching" target="_blank">Mobile cache</a> works safest with both options enabled. When in doubt, keep both.', 'rocket' ),
		),
	)
);

/**
 * User cache
 */
add_settings_field(
	'rocket_logged_user',
	__( 'User cache:', 'rocket' ),
	'rocket_field', 'rocket_basic',
	'rocket_display_main_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Enable caching for logged-in WordPress users', 'rocket' ),
			'label_for'    => 'cache_logged_user',
			'label_screen' => __( 'User cache:', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'user_cache_desc',
			'description'  =>
			/* translators: line break is recommended, but not mandatory  */
			__( '<a href="http://docs.wp-rocket.me/article/313-logged-in-user-cache" target="_blank">User cache</a> is great when you have user-specific or restricted content on your website.', 'rocket' ),
		),
	)
);

/**
 * SSL cache
 */
add_settings_field(
	'rocket_ssl',
	__( 'SSL cache:', 'rocket' ),
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Enable caching for pages with <code>https://</code>', 'rocket' ),
			'label_for'    => 'cache_ssl',
			'label_screen' => __( 'SSL cache:', 'rocket' ),
			'default'	   => ( rocket_is_ssl_website() ) ? 1 : get_rocket_option( 'ssl', 0 ),
			'readonly'	   => rocket_is_ssl_website(),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'ssl_cache_desc',
			'description'  =>
			/* translators: line break is recommended, but not mandatory  */
			__( '<a href="http://docs.wp-rocket.me/article/314-using-ssl-with-wp-rocket" target="_blank">SSL cache</a> works best when your entire website runs on HTTPS.', 'rocket' ),
		),
	)
);

/**
 * Emoji cache
 */
add_settings_field(
	'rocket_wordpress_emojis',
	__( 'Emoji cache:', 'rocket' ),
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Use default emoji of visitors’ browser instead of loading emoji from WordPress.org', 'rocket' ),
			'label_for'    => 'emoji',
			'label_screen' => __( 'Emoji cache:', 'rocket' ),
		),
		array(
			'type'         => 'helper_performance',
			'name'         => 'emoji_perf_tip',
			'description'  => __( 'Reduces the number of HTTP requests, can improve loading time.', 'rocket' )
		),
	)
);

/**
 * Disable Embeds
 */
add_settings_field(
	'rocket_wordpress_embeds',
	__( 'Embeds:', 'rocket' ),
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Disable WordPress Embeds', 'rocket' ),
			'label_for'    => 'embeds',
			'label_screen' => __( 'Embeds:', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'embeds',
			'description'  => __( 'Prevents others from embedding content from your site, prevents you from embedding content from other (non-whitelisted) sites, and removes JavaScript requests related to <a href="https://wordpress.org/news/2015/12/clifford/">WordPress Embeds</a>.', 'rocket' ),
		),
	)
);

/**
 * Cache lifespan
 */
$rocket_purge_fields = array(
	array(
		'type'         => 'helper_help',
		'name'         => 'purge_tip',
		'description'  => __( 'Specify time after which the global cache gets cleared (0 = unlimited)', 'rocket' ),
	),
	array(
		'type'         => 'number',
		'label_for'    => 'purge_cron_interval',
		'label_screen' => __( 'Clear cache after …', 'rocket' ),
		'fieldset'     => 'start',
	),
	array(
		'type'         => 'select',
		'label_for'    => 'purge_cron_unit',
		'label_screen' => __( 'Unit of time', 'rocket' ),
		'fieldset'     => 'end',
		'options'      => array(
			'MINUTE_IN_SECONDS' => __( 'minute(s)', 'rocket' ),
			'HOUR_IN_SECONDS'   => __( 'hour(s)', 'rocket' ),
			'DAY_IN_SECONDS'    => __( 'day(s)', 'rocket' ),
		),
	),
	array(
		'type'         => 'helper_description',
		'name'         => 'purge',
		'description'  => sprintf(
			/* translators: %s = preload tab ID */
			__( 'Cache lifespan is the period of time after which all cache files get removed. Enable <a href="%s">Preloading</a> for the cache to be rebuilt automatically after lifespan expiration.', 'rocket' ),
			'#tab_preload'
		),
	),
);

$rocket_purge_fields[] = array(
	'type'         => 'helper_warning',
	'name'         => 'purge_warning_less',
// @todo Replace link with one from our documentation!
	'description'  => __( 'Reduce lifespan to less hours in case you notice issues that seem to appear only frequently. <a href="https://joshpress.net/wordpress-nonces-and-wordpress-caching/" target="_blank">Why?</a>', 'rocket' ),
);


$rocket_purge_fields[] = array(
		'type'         => 'helper_warning',
		'name'         => 'purge_warning_more',
		'description'  => __( 'Increase lifespan to a few hours in case you notice server issues with this setting.', 'rocket' ),
	);

/* Cache lifespan option */
add_settings_field(
	'rocket_purge',
	__( 'Cache lifespan', 'rocket' ),
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	$rocket_purge_fields
);
