<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_main_options', __( 'Basic options', 'rocket' ), '__return_false', 'rocket_basic' );

// Mobile plugins list.
$mobile_plugins = array(
	'<a href="https://wordpress.org/plugins/wptouch/" target="_blank">WP Touch (Free version only)</a>',
	'<a href="https://wordpress.org/plugins/wiziapp-create-your-own-native-iphone-app" target="_blank">wiziApp</a>',
	'<a href="https://wordpress.org/plugins/wordpress-mobile-pack/" target="_blank">WordPress Mobile Pack</a>',
	'<a href="https://wordpress.org/plugins/wp-mobilizer/" target="_blank">WP-Mobilizer</a>',
	'<a href="https://wordpress.org/plugins/wp-mobile-edition/" target="_blank">WP Mobile Edition</a>',
	'<a href="https://wordpress.org/plugins/device-theme-switcher/" target="_blank">Device Theme Switcher</a>',
	'<a href="https://wordpress.org/plugins/wp-mobile-detect/" target="_blank">WP Mobile Detect</a>',
	'<a href="https://codecanyon.net/item/easy-social-share-buttons-for-wordpress/6394476" target="_blank">Easy Social Share Buttons</a>',
);

add_settings_field(
	'rocket_mobile',
	__( 'Mobile cache:', 'rocket' ),
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	array(
		array(
			'type'		   => 'checkbox',
			'label'		   => __( 'Enable caching for mobile devices.', 'rocket' ),
			'label_for'	   => 'cache_mobile',
			'label_screen' => __( 'Mobile cache:', 'rocket' ),
			'default'	   => ( rocket_is_mobile_plugin_active() ) ? 1 : get_rocket_option( 'cache_mobile', 0 ),
			'readonly'	   => rocket_is_mobile_plugin_active(),
		),
		array(
			'parent'	   => 'cache_mobile',
			'type'         => 'checkbox',
			'label'        => __( 'Create a separate caching file for mobile visitors.', 'rocket' ),
			'name'         => 'do_caching_mobile_files',
			'default'	   => ( rocket_is_mobile_plugin_active() ) ? 1 : get_rocket_option( 'do_caching_mobile_files', 0 ),
			'readonly'	   => rocket_is_mobile_plugin_active(),
		),
		array(
			'parent'	   => 'cache_mobile',
			'type'         => 'helper_description',
			'name'         => 'mobile',
			'description'  => __( 'Are you using a dedicated mobile theme or <code>wp_is_mobile()</code>? If so, you should activate this option to serve a specific caching file for your mobile visitors.', 'rocket' ),
		),
		array(
			'parent'	   => 'cache_mobile',
			'type'         => 'helper_warning',
			'name'         => 'mobile',
			'description'  => wp_sprintf( __( 'If you are using one of these plugins, you must activate this option: %l', 'rocket' ), $mobile_plugins ),
		),
	)
);
add_settings_field(
	'rocket_logged_user',
	__( 'Logged in user cache:', 'rocket' ),
	'rocket_field', 'rocket_basic',
	'rocket_display_main_options',
	array(
		array(
		    'type'         => 'checkbox',
		    'label'        => __( 'Enable caching for logged in users.', 'rocket' ),
		    'label_for'    => 'cache_logged_user',
		    'label_screen' => __( 'Logged in user cache:', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_ssl',
	__( 'SSL cache:', 'rocket' ),
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	array(
		'type'         => 'checkbox',
		'label'        => __( 'Enable caching for pages with SSL protocol (<code>https://</code>).', 'rocket' ),
		'label_for'    => 'cache_ssl',
		'label_screen' => __( 'SSL cache:', 'rocket' ),
		'default'	   => ( rocket_is_ssl_website() ) ? 1 : get_rocket_option( 'ssl', 0 ),
		'readonly'	   => rocket_is_ssl_website(),
	)
);
add_settings_field(
	'rocket_wordpress_emojis',
	__( 'Emojis:', 'rocket' ),
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Replace emojis with default WordPress smileys.', 'rocket' ),
			'label_for'    => 'emoji',
			'label_screen' => __( 'Emojis:', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'emoji',
			'description'  => __( '<strong>Note:</strong> By activating this option, you will reduce the number of external HTTP requests.', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_wordpress_embeds',
	__( 'Embeds:', 'rocket' ),
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Disable enhanced WordPress embeds.', 'rocket' ),
			'label_for'    => 'embeds',
			'label_screen' => __( 'Embeds:', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'embeds',
			'description'  => __( '<strong>Note:</strong> By activating this option, you will prevent others from embedding your site, prevent you from embedding other non-whitelisted sites and disables all JavaScript related to the feature.', 'rocket' ),
		),
	)
);

add_settings_field(
	'rocket_purge',
	__( 'Clear Cache Lifespan', 'rocket' ),
	'rocket_field',
	'rocket_basic',
	'rocket_display_main_options',
	array(
		array(
			'type'         => 'number',
			'label_for'    => 'purge_cron_interval',
			'label_screen' => __( 'Clear Cache Lifespan', 'rocket' ),
			'fieldset'     => 'start',
		),
		array(
			'type'		   => 'select',
			'label_for'	   => 'purge_cron_unit',
			'label_screen' => __( 'Unit of time', 'rocket' ),
			'fieldset'	   => 'end',
			'options' => array(
				'MINUTE_IN_SECONDS' => __( 'minute(s)', 'rocket' ),
				'HOUR_IN_SECONDS'   => __( 'hour(s)', 'rocket' ),
				'DAY_IN_SECONDS'    => __( 'day(s)', 'rocket' ),
			),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'purge',
			'description'  => __( 'By default, cache lifespan is 24 hours. This means that once created, the cache files are automatically removed after 24 hours before being recreated.', 'rocket' ) . '<br/>' . __( 'This can be useful if you display your latest tweets or rss feeds in your sidebar, for example.', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'purge',
			'description'  => __( 'Specify 0 for unlimited lifetime.', 'rocket' ),
			),
		)
);
