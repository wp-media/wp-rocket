<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

// Are we white-labeled?
$rwl = rocket_is_white_label();

add_settings_section( 'rocket_display_preload_options', __( 'Preload options', 'rocket' ), '__return_false', 'rocket_preload' );

/**
 * Sitemap preload
 */
$sitemap_preload_options = array(
	array(
		'type'         => 'checkbox',
		'label'        => __( 'Activate sitemap-based cache preloading', 'rocket' ),
		'label_for'    => 'sitemap_preload',
		'name'         => 'sitemap_preload',
		'label_screen' => __( 'Activate sitemap-based cache preloading', 'rocket' ),
		'default'      => 0,
	),
	array(
			'type'          => 'helper_description',
			'name'          => 'sitemaps_preload_desc',
			'description'   => $rwl ? __( 'Sitemap preloading runs automatically when the cache lifespan expires. You can also launch it manually from the upper toolbar menu, or from the Tools tab.', 'rocket' ) : __( '<a href="http://docs.wp-rocket.me/article/8-how-the-cache-is-preloaded" target="_blank">Sitemap preloading</a> runs automatically when the cache lifespan expires. You can also launch it manually from the upper toolbar menu, or from the Tools tab.', 'rocket' ),
		),
);

add_settings_field(
	'rocket_sitemap_preload_activate',
	 __( 'Sitemap preloading:', 'rocket' ),
	'rocket_field',
	'rocket_preload',
	'rocket_display_preload_options',
	/**
	 * Filters the array of options activating the sitemap preloading
	 *
	 * @since 2.8
	 *
	 * @param array $sitemap_preload_options Array of options arrays.
	 */
	apply_filters( 'rocket_sitemap_preload_options', $sitemap_preload_options )
);

/**
 * Sitemap preload interval
 */
add_settings_field(
	'rocket_sitemap_preload_interval',
	 __( 'Sitemap crawl interval:', 'rocket' ),
	'rocket_field',
	'rocket_preload',
	'rocket_display_preload_options',
	array(
		array(
			'type'         => 'select',
			'label'        => '&#160;' . __( '<span class="screen-reader-text">This is the </span>waiting time between each URL crawl', 'rocket' ),
			'label_for'    => 'sitemap_preload_url_crawl',
			'label_screen' => __( 'Sets the intervall between each URL crawl', 'rocket' ),
			/**
			 * Filters the array of options interval for sitemap preload
			 *
			 * @since 2.8
			 *
			 * @param array $intervals Array of options interval defined by a $value => $label pair.
			 */
			'options'      => apply_filters( 'rocket_sitemap_preload_interval', array(
				'250000'  => '250ms',
				'500000'  => '500ms',
				'750000'  => '750ms',
				'1000000' => '1s',
				'2000000' => '2s',
			) ),
		),
		array(
			'type'        => 'helper_description',
			'name'        => 'sitemaps_preload_url_crawl_desc',
		),
		array(
			'type'        => 'helper_warning',
			'name'        => 'sitemaps_preload_url_crawl_warning',
			'description' => __( 'Set a higher value if you notice any overload on your server!', 'rocket' ),
		),
	)
);

/**
 * Sitemaps for preloading
 */
add_settings_field(
	'rocket_sitemap_preload_files',
	 __( 'Sitemaps for preloading:', 'rocket' ),
	'rocket_field',
	'rocket_preload',
	'rocket_display_preload_options',
	array(
		array(
			'type'        => 'helper_help',
			'name'        => 'sitemaps_list_desc',
			'description' => __( 'Specify XML sitemap(s) to be used for preloading (one per line)', 'rocket' ),
		),
		array(
			'type'         => 'textarea',
			'label'        => __( 'Sitemap files to use for preloading', 'rocket' ),
			'name'         => 'sitemaps',
			'label_screen' => __( 'The sitemap files to use for preloading the cache', 'rocket' ),
			'placeholder'  => sprintf( '%s/sitemap.xml', get_home_url() ),
		),
	)
);

/**
 * Preload bot
 */
add_settings_field(
	'rocket_enable_bot_preload',
	__( 'Preload bot:', 'rocket' ),
	'rocket_field',
	'rocket_preload',
	'rocket_display_preload_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Manual', 'rocket' ),
			'label_for'    => 'manual_preload',
			'label_screen' => __( 'Activate manual preload (from upper toolbar, or from Tools tab of WP Rocket)', 'rocket' ),
			'default'      => 1,
		),
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Automatic', 'rocket' ),
			'label_for'    => 'automatic_preload',
			'label_screen' => __( 'Activate automatic preload after content updates', 'rocket' ),
			'default'      => 1,
		),
		array(
			'type'        => 'helper_description',
			'name'        => 'bot_preload',
			'description' => $rwl ? __( 'Bot-based preloading should only be used on well-performing servers. Once activated, it gets triggered automatically after you add or update content on your website. You can also launch it manually from the upper toolbar menu, or from the Tools tab.', 'rocket' ) : __( '<a href="http://docs.wp-rocket.me/article/8-how-the-cache-is-preloaded" target="_blank">Bot-based preloading</a> should only be used on well-performing servers. Once activated, it gets triggered automatically after you add or update content on your website. You can also launch it manually from the upper toolbar menu, or from the Tools tab.', 'rocket' ),
		),
		array(
			'type'        => 'helper_warning',
			'name'        => 'bot_preload_warning',
			'description' => __( 'Deactivate these options if you notice any overload on your server!', 'rocket' ),
		),
	)
);

/**
 * DNS Prefetch
 */
add_settings_field(
	'rocket_dns_prefetch',
	__( 'Prefetch DNS requests:', 'rocket' ),
	'rocket_field',
	'rocket_preload',
	'rocket_display_preload_options',
	array(
		array(
			'type'         => 'helper_help',
			'name'         => 'dns_prefetch_tip',
			'description'  => __( 'Specify external hosts to be prefetched (no <code>http:</code>, one per line)', 'rocket' ),
			),
			array(
				'type'         => 'textarea',
				'label_for'    => 'dns_prefetch',
				'label_screen' => __( 'Prefetch DNS requests:', 'rocket' ),
				'placeholder'  => '//example.com',
			),
			array(
				'type'         => 'helper_description',
				'name'         => 'dns_prefetch',
				'description'  => __( '<a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-DNS-Prefetch-Control" target="_blank">DNS prefetching</a> can make external files load faster, especially on mobile networks.', 'rocket' ),
			),
	)
);
