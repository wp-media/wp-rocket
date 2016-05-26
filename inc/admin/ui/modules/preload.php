<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_preload_options', __( 'Preload options', 'rocket' ), '__return_false', 'rocket_preload' );
add_settings_field(
	'rocket_enable_bot_preload',
	__( 'Activate preload bot:', 'rocket' ),
	'rocket_field',
	'rocket_preload',
	'rocket_display_preload_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __('Manual', 'rocket' ),
			'label_for'    => 'manual_preload',
			'label_screen' => __( 'Activate manual preload (from admin bar or Tools tab of WP Rocket)', 'rocket' ),
			'default'      => 1,
		),
		array(
			'type'         => 'checkbox',
			'label'        => __('Automatic', 'rocket' ),
			'label_for'    => 'automatic_preload',
			'label_screen' => __( 'Activate automatic preload after partial cache clearing', 'rocket' ),
			'default'      => 1,
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'bot_preload',
			'description'  => __( 'WP Rocket uses a bot to preload your content and create the cached files. You can deactivate it if you need to.', 'rocket') . '<br>' . __( 'Manual preload is launched from the admin bar menu or from the Tools tab of WP Rocket.', 'rocket' ) . '<br>' . __( 'Automatic preload is launched after you add/update content on your website.', 'rocket') . '<br>' . __( 'You can read our documentation to learn more about it: <a href="http://docs.wp-rocket.me/article/8-how-the-cache-is-preloaded" target="_blank">http://docs.wp-rocket.me/article/8-how-the-cache-is-preloaded</a>', 'rocket'),
		),
	)
);

$sitemap_preload_options = array(
    array(
        'type'         => 'checkbox',
	    'label'        => __('Activate the sitemap preloading', 'rocket' ),
	    'label_for'    => 'sitemap_preload',
	    'name'         => 'sitemap_preload',
	    'label_screen' => __( 'Activate sitemap preloading', 'rocket' ),
	    'default'      => 0,
    ),
    array(
			'type'			=> 'helper_description',
			'name'			=> 'sitemaps_preload_desc',
			'description'  => __( 'The sitemap preloading can be launched from the admin bar menu or the Tools tab of WP Rocket. It will also automatically run when the lifespan of the Clear Cache Lifespan option expires.', 'rocket' )
		),
);
        

add_settings_field(
	'rocket_sitemap_preload_activate',
	 __( 'Sitemap preloading:', 'rocket' ),
	'rocket_field',
	'rocket_preload',
	'rocket_display_preload_options',
	/*
     * Filters the array of options activating the sitemap preloading
     *
     * @since 2.8
     *
     * @param array $sitemap_preload_options Array of options arrays
     */
    apply_filters( 'rocket_sitemap_preload_options', $sitemap_preload_options )
);

add_settings_field(
	'rocket_sitemap_preload_interval',
	 __( 'URL crawl interval:', 'rocket' ),
	'rocket_field',
	'rocket_preload',
	'rocket_display_preload_options',
	array(
        array(
            'type'         => 'select',
			'label'        => __('Interval between each URL crawl', 'rocket' ),
			'label_for'    => 'sitemap_preload_url_crawl',
			'label_screen' => __( 'Interval between each URL crawl', 'rocket' ),
			/*
             * Filters the array of options interval for sitemap preload
             *
             * @since 2.8
             *
             * @param array Array of options interval defined by a $value => $label pair
             */
			'options'      => apply_filters( 'rocket_sitemap_preload_interval', array(
    			'250000'      => '250ms',
    			'500000'      => '500ms',
    			'750000'      => '750ms',
    			'1000000'     => '1s',
    			'2000000'     => '2s',
			) )
        ),
        array(
			'type'			=> 'helper_description',
			'name'			=> 'sitemaps_preload_url_crawl_desc',
			'description'  => __( 'You can use this option to change the interval between each URL crawl.', 'rocket' )
		),
		array(
			'type'			=> 'helper_warning',
			'name'			=> 'sitemaps_preload_url_crawl_warning',
			'description'  => __( 'Depending on your server, it might be necessary to set a higher value to prevent any overload. Default is 500ms.', 'rocket' )
		),
	)
);

add_settings_field(
	'rocket_sitemap_preload_files',
	 __( 'XML sitemaps to use for preloading:', 'rocket' ),
	'rocket_field',
	'rocket_preload',
	'rocket_display_preload_options',
	array(
		array(
			'type'         => 'textarea',
			'label'        => __( 'Sitemap files to use for preloading', 'rocket' ),
			'name'         => 'sitemaps',
			'label_screen' => __( 'The sitemap files to use for preloading the cache', 'rocket' )
		),
		array(
			'type'			=> 'helper_description',
			'name'			=> 'sitemaps_list_desc',
			'description'  => __( 'Enter the URL of the XML sitemap files (one per line).', 'rocket' )
		),
	)
);