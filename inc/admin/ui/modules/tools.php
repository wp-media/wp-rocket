<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

// Are we white-labeled?
$rwl = rocket_is_white_label();

add_settings_section( 'rocket_display_tools', __( 'Tools', 'rocket' ), '__return_false', 'rocket_tools' );

/**
 * Beta testing
 */
if ( ! $rwl ) {

	add_settings_field(
		'rocket_do_beta',
		__( 'Beta testing:', 'rocket' ),
		'rocket_field',
		'rocket_tools',
		'rocket_display_tools',
		array(
			array(
				'type'         => 'checkbox',
				'label'        => __( 'Yes, I would like to receive beta versions of WP Rocket as updates. I understand that beta versions may contain unstable code, and I know how to roll back to a stable version manually if I have to.', 'rocket' ),
				'label_for'    => 'do_beta',
				'label_screen' => __( 'Beta Testing', 'rocket' ),
			),
		)
	);
}

/**
 * Clear cache
 */
add_settings_field(
	'rocket_purge_all',
	__( 'Clear cache:', 'rocket' ),
	'rocket_button',
	'rocket_tools',
	'rocket_display_tools',
	array(
		'helper_help' => array(
			'name'         => 'purge_all',
			'description'  => __( 'Clear the cache for the whole website.', 'rocket' ),
		),
		'button' => array(
			'button_label' => __( 'Clear cache', 'rocket' ),
			'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=all' ), 'purge_cache_all' ),
		),
	)
);

/**
 * Preload
 */
add_settings_field(
	'rocket_preload',
	__( 'Preload cache:', 'rocket' ),
	'rocket_button',
	'rocket_tools',
	'rocket_display_tools',
	array(
		'helper_help' => array(
			'name'         => 'preload',
			'description'  => sprintf(
				/* translators: %s = tab anchor */
				__( 'Preload the cache according to your <a href="%s">Preload settings</a>.', 'rocket' ),
				'#tab_preload'
			)
		),
		'button' => array(
			'button_label' => __( 'Preload cache', 'rocket' ),
			'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=preload' ), 'preload' ),
		),
	)
);

/**
 * Clear OPcache
 */
if ( function_exists( 'opcache_reset' ) ) {

	add_settings_field(
		'rocket_purge_opcache',
		__( 'Purge OPcache:', 'rocket' ),
		'rocket_button',
		'rocket_tools',
		'rocket_display_tools',
		array(
			'button' => array(
				'button_label' => __( 'Purge OPcache', 'rocket' ),
				'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=rocket_purge_opcache' ), 'rocket_purge_opcache' ),
			),
		)
	);
}

/**
 * Export
 */
add_settings_field(
	'rocket_export_options',
	__( 'Export settings:', 'rocket' ),
	'rocket_field',
	'rocket_tools',
	'rocket_display_tools',
	array( 'type' => 'rocket_export_form', 'name' => 'export' )
);

/**
 * Import
 */
add_settings_field(
	'rocket_import_options',
	__( 'Import settings:', 'rocket' ),
	'rocket_field',
	'rocket_tools',
	'rocket_display_tools',
	array( 'type' => 'rocket_import_upload_form' )
);

/**
 * Rollback
 */
if ( current_user_can( 'update_plugins' ) ) {
	$temp_description = sprintf(
		/* translators: %s = button text */
		__( 'Backup your settings with the “%s” button above before you re-install a previous version.', 'rocket' ),
		_x( 'Download settings', 'button text', 'rocket' )
	);
	add_settings_field(
		'rocket_rollback',
		__( 'Rollback:', 'rocket' ),
		'rocket_button',
		'rocket_tools',
		'rocket_display_tools',
		array(
			'helper_warning' => array(
				'name'         => 'rollback2',
				'description'  => $temp_description,
			),
			'button' => array(
				'button_label' => sprintf( __( 'Re-install version %s', 'rocket' ), WP_ROCKET_LASTVERSION ),
				'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=rocket_rollback' ), 'rocket_rollback' ),
			),
			'helper_description' => array(
				'name'         => 'rollback',
				'description'  => $rwl ? sprintf(
					/* translators: %s = version number */
					__( 'Has version %1$s caused an issue on your website? You can roll back to the previous major version here.', 'rocket' ),
					WP_ROCKET_VERSION
				) : sprintf(
					/* translators: %1$s = version number; %2$s = tab anchor */
					__( 'Has version %1$s caused an issue on your website?<br>You can roll back to the previous major version here. Then <a href="%2$s">send us a support request</a>.', 'rocket' ),
					WP_ROCKET_VERSION,
					'#tab_support'
				),
			),
		)
	);
}
unset( $temp_description );
