<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_tools', __( 'Tools', 'rocket' ), '__return_false', 'rocket_tools' );

if ( ! rocket_is_white_label() ) {
	add_settings_field(
		'rocket_do_beta',
		__( 'Beta Tester', 'rocket' ),
		'rocket_field',
		'rocket_tools',
		'rocket_display_tools',
		array(
			array(
				'type'         => 'checkbox',
				'label'        => __( 'Yes I do!', 'rocket' ),
				'label_for'    => 'do_beta',
				'label_screen' => __( 'Beta Tester', 'rocket' )
			),
			array(
				'type' 		  => 'helper_description',
				'name' 		  => 'do_beta',
				'description' => __( 'Check it to participate in the WP Rocket Beta Program and get earlier access to new versions, thanks in advance.', 'rocket' )
			)
		)
    );
}

add_settings_field(
	'rocket_purge_all',
	__( 'Clear cache', 'rocket' ),
	'rocket_button',
	'rocket_tools',
	'rocket_display_tools',
	array(
		'button'=>array(
			'button_label' => __( 'Clear cache', 'rocket' ),
			'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=all' ), 'purge_cache_all' ),
		),
		'helper_description'=>array(
			'name'         => 'purge_all',
			'description'  => __( 'Clear the cache for the whole site.', 'rocket' )
		),
	)
);
add_settings_field(
	'rocket_preload',
	__( 'Preload cache', 'rocket' ),
	'rocket_button',
	'rocket_tools',
	'rocket_display_tools',
	array(
        'button'=>array(
        	'button_label' => __( 'Preload cache', 'rocket' ),
        	'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=preload' ), 'preload' ),
        ),
		'helper_description'=>array(
			'name'         => 'preload',
        	'description'  => __( 'Allows you to request a bot crawl to preload the cache (homepage and its internal links).', 'rocket' )
		),
	)
);

if ( function_exists( 'opcache_reset' ) ) {
    add_settings_field(
    	'rocket_purge_opcache',
    	__( 'Purge OPcache', 'rocket' ),
    	'rocket_button',
    	'rocket_tools',
    	'rocket_display_tools',
    	array(
            'button'=>array(
            	'button_label' => __( 'Purge OPcache', 'rocket' ),
            	'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=rocket_purge_opcache' ), 'rocket_purge_opcache' ),
            )
    	)
    );
}

add_settings_field(
	'rocket_export_options',
	__( 'Settings Exporter', 'rocket' ),
	'rocket_field',
	'rocket_tools',
	'rocket_display_tools',
	array( 'type'=>'rocket_export_form', 'name'=>'export' )

);

add_settings_field(
	'rocket_import_options',
	__( 'Settings Importer', 'rocket' ),
	'rocket_field',
	'rocket_tools',
	'rocket_display_tools',
	array( 'type'=>'rocket_import_upload_form' )

);

if ( current_user_can( 'update_plugins' ) ) {
	$temp_description = __( 'Please backup your settings before, use the "Download options" button above.', 'rocket' );
    add_settings_field(
		'rocket_rollback',
		__( 'Update Rollback', 'rocket' ),
		'rocket_button',
		'rocket_tools',
		'rocket_display_tools',
		array(
	        'button'=>array(
	        	'button_label' => sprintf( __( 'Reinstall v%s', 'rocket' ), WP_ROCKET_LASTVERSION ),
	        	'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=rocket_rollback' ), 'rocket_rollback' ),
	        ),
			'helper_description'=>array(
				'name'         => 'rollback',
	        	'description'  => sprintf( __( 'Is the version %s causing you some issues? You can ask for a rollback and reinstall the last version you used before.', 'rocket' ), WP_ROCKET_VERSION )
			),
			'helper_warning'=>array(
				'name'         => 'rollback2',
	        	'description'  => $temp_description,
			),
		)

    );
}
unset( $temp_description );