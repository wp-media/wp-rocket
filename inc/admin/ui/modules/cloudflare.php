<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_cloudflare_options', 'CloudFlare', '__return_false', 'cloudflare' );
add_settings_field(
	'rocket_cloudflare_email',
	__( 'CloudFlare Account Email', 'rocket' ),
	'rocket_field',
	'cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'text',
			'label_for'    => 'cloudflare_email',
			'label_screen' => __( 'CloudFlare Account Email', 'rocket' ),
		)
	)
);
add_settings_field(
	'rocket_cloudflare_api_key',
	__( 'API Key', 'rocket' ),
	'rocket_field',
	'cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'text',
			'label_for'    => 'cloudflare_api_key',
			'label_screen' => __( 'API Key', 'rocket' ),
		),
		array(
			'type' 		   => 'helper_description',
			'name'         => 'cloudflare_api_key',
			'description'  => sprintf( __( '<strong>Note:</strong> Where do I find my CloudFlare API key? <a href=%s"">Learn more</a>', 'rocket' ), 'https://support.cloudflare.com/hc/en-us/articles/200167836-Where-do-I-find-my-CloudFlare-API-key-' ),
		)
	)
);
add_settings_field(
	'rocket_cloudflare_domain',
	__( 'Domain', 'rocket' ),
	'rocket_field',
	'cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'text',
			'label_for'    => 'cloudflare_domain',
			'label_screen' => __( 'Domain', 'rocket' ),
		)
	)
);
add_settings_field(
	'rocket_cloudflare_devmode',
	__( 'Development Mode', 'rocket' ),
	'rocket_field',
	'cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'select',
			'label_for'    => 'cloudflare_devmode',
			'label_screen' => __( 'Development Mode', 'rocket' ),
			'options'	   => array(
				0 => __( 'Off', 'rocket' ),
				1 => __( 'On', 'rocket' )
			),
		),
		array(
			'type' 		   => 'helper_description',
			'name'         => 'cloudflare_devmode',
			'description'  => sprintf( __( 'Temporarily enter development mode on your website. <a href=%s"">Learn more</a>', 'rocket' ), 'https://support.cloudflare.com/hc/en-us/articles/200168246' ),
		)
	)
);
add_settings_field(
	'rocket_cloudflare_auto_settings',
	__( 'Auto enable the optimal CloudFlare settings (props WP Rocket)', 'rocket' ),
	'rocket_field',
	'cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'select',
			'label_for'    => 'cloudflare_auto_settings',
			'label_screen' => __( 'Auto enable the optimal CloudFlare settings (props WP Rocket)', 'rocket' ),
			'options'	   => array(
				0 => __( 'No', 'rocket' ),
				1 => __( 'Yes', 'rocket' )
			),
		),
		array(
			'type' 		   => 'helper_description',
			'name'         => 'cloudflare_auto_settings',
			'description'  => __( 'We select the best CloudFlare configuration for speed, performance grade and compatibility.', 'rocket' ),
		)
	)
);
add_settings_field(
	'rocket_purge_cloudflare',
	__( 'Clear cache', 'rocket' ),
	'rocket_button',
	'cloudflare',
	'rocket_display_cloudflare_options',
	array(
		'button'=>array(
			'button_label' => __( 'Clear cache', 'rocket' ),
			'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=rocket_purge_cloudflare' ), 'rocket_purge_cloudflare' ),
		),
		'helper_description'=>array(
			'name'         => 'purge_cloudflare',
			'description'  => sprintf(__( 'Immediately purge cached resources for your website. <a href="%s" target="_blank">Learn more</a>', 'rocket' ), 'https://support.cloudflare.com/hc/en-us/articles/200169246' )
		),
	)
);