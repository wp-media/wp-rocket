<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
  * Allow to display the "Varnish" tab in the settings page
  *
  * @since 2.7
  *
  * @param bool true will display the "Varnish" tab
 */
if ( apply_filters( 'rocket_display_varnish_options_tab', true ) ) :

add_settings_section( 'rocket_display_main_options', 'Varnish', '__return_false', 'rocket_varnish' );

add_settings_field(
	'rocket_varnish_auto_purge',
	__( 'Varnish Caching Purge', 'rocket' ),
	'rocket_field',
	'rocket_varnish',
	'rocket_display_main_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __('Enable the Varnish caching auto-purge.', 'rocket' ),
			'label_for'    => 'varnish_auto_purge',
			'label_screen' => __( 'Varnish Caching Purge', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'varnish_auto_purge',
			'description'  => __( 'The Varnish cache will be purged each time WP Rocket cache needs to be cleared to avoid conflict.', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'mobile',
			'description'  => sprintf( __( '<strong>Note:</strong> If your server is using %sVarnish%s, you must activate this option.', 'rocket' ), '<a href="https://www.varnish-cache.org/" target="_blank">', '</a>' ),
		),
	)
);

endif;