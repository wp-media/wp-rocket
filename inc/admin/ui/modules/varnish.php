<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

// Are we white-labeled?
$rwl = rocket_is_white_label();

/**
	* Allow to display the "Varnish" tab in the settings page
	*
	* @since 2.7
	*
	* @param bool true will display the "Varnish" tab
 */
if ( ! apply_filters( 'rocket_display_varnish_options_tab', true ) ) {
	return false;
}

add_settings_section( 'rocket_display_main_options', 'Varnish', '__return_false', 'rocket_varnish' );

/**
 * Panel caption
 */
if ( ! $rwl ) {

	add_settings_field(
		'rocket_varnish_options_panel',
		false,
		'rocket_field',
		'rocket_varnish',
		'rocket_display_main_options',
		array(
			array(
				'type'         => 'helper_panel_description',
				'name'         => 'varnish_options_panel_caption',
				'description'  => sprintf(
					'<span class="dashicons dashicons-editor-help" aria-hidden="true"></span><strong>%1$s</strong>',
					sprintf(
						/* translators: line break recommended, but not mandatory */
						__( 'If <a href="%s" target="_blank">Varnish</a> runs on your server, you must activate the option below.<br>You would know if Varnish is active. If you don’t know, you can safely ignore this setting.', 'rocket' ),
						'https://www.varnish-cache.org/'
					)
				),
			),
		)
	);
}

/**
 * Varnish on/off
 */
add_settings_field(
	'rocket_varnish_auto_purge',
	__( 'Sync Varnish cache:', 'rocket' ),
	'rocket_field',
	'rocket_varnish',
	'rocket_display_main_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Purge Varnish cache automatically', 'rocket' ),
			'label_for'    => 'varnish_auto_purge',
			'label_screen' => __( 'Sync Varnish cache with plugin cache', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'varnish_auto_purge',
			'description'  => sprintf(
				/* translators: %s = “WP Rocket” or white-label plugin name */
				__( 'Varnish cache will be purged each time %s clears its cache to ensure content is always up to date.', 'rocket' ),
				WP_ROCKET_PLUGIN_NAME
			)
		),
	)
);
