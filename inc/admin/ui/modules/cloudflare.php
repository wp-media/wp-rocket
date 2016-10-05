<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_cloudflare_options', 'CloudFlare', '__return_false', 'rocket_cloudflare' );
add_settings_field(
	'rocket_cloudflare_email',
	__( 'CloudFlare Account Email', 'rocket' ),
	'rocket_field',
	'rocket_cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'text',
			'label_for'    => 'cloudflare_email',
			'label_screen' => __( 'CloudFlare Account Email', 'rocket' ),
		)
	)
);

if ( ! defined( 'WP_ROCKET_CF_API_KEY_HIDDEN' ) || ! WP_ROCKET_CF_API_KEY_HIDDEN ) :

add_settings_field(
	'rocket_cloudflare_api_key',
	__( 'API Key', 'rocket' ),
	'rocket_field',
	'rocket_cloudflare',
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
			'description'  => sprintf( __( '<strong>Note:</strong> Where do I find my CloudFlare API key? <a href="%s" target="_blank">Learn more</a>', 'rocket' ), 'https://support.cloudflare.com/hc/en-us/articles/200167836-Where-do-I-find-my-CloudFlare-API-key-' ),
		)
	)
);

endif;

add_settings_field(
	'rocket_cloudflare_domain',
	__( 'Domain', 'rocket' ),
	'rocket_field',
	'rocket_cloudflare',
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
	'rocket_cloudflare',
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
			'description'  => sprintf( __( 'Temporarily enter development mode on your website. <a href="%s" target="_blank">Learn more</a>', 'rocket' ), 'https://support.cloudflare.com/hc/en-us/articles/200168246' ),
		)
	)
);

if ( rocket_is_white_label() ) {
    $rocket_cloudflare_auto_settings_label = __( 'Auto enable the optimal CloudFlare settings', 'rocket' );
} else {
    $rocket_cloudflare_auto_settings_label = __( 'Auto enable the optimal CloudFlare settings (props WP Rocket)', 'rocket' );
}

add_settings_field(
	'rocket_cloudflare_auto_settings',
	$rocket_cloudflare_auto_settings_label,
	'rocket_field',
	'rocket_cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'select',
			'label_for'    => 'cloudflare_auto_settings',
			'label_screen' => $rocket_cloudflare_auto_settings_label,
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
	'rocket_cloudflare_protocol_rewrite',
	__( 'HTTPS Protocol Rewrite', 'rocket' ),
	'rocket_field',
	'rocket_cloudflare',
	'rocket_display_cloudflare_options',
	array(
		array(
			'type'         => 'select',
			'label_for'    => 'cloudflare_protocol_rewrite',
			'label_screen' => __( 'HTTPS Protocol Rewrite', 'rocket' ),
			'options'	   => array(
				0 => __( 'Off', 'rocket' ),
				1 => __( 'On', 'rocket' )
			),
		),
		array(
			'type' 		   => 'helper_description',
			'name'         => 'cloudflare_protocol_rewrite',
			'description'  => sprintf( 
				__( 'Rewrite all images, stylesheets and scripts from using either %1$s or %2$s to using just %3$s to support %4$s.', 'rocket' ),
				'<code>http://</code>', 
				'<code>https://</code>', 
				'<code>//</code>', 
				'<a href="https://support.cloudflare.com/hc/en-us/articles/200170416-What-do-the-SSL-options-Off-Flexible-SSL-Full-SSL-Full-SSL-Strict-mean-" target="_blank">Flexible SSL</a>'
			),
		)
	)
);
add_settings_field(
	'rocket_purge_cloudflare',
	__( 'Clear cache', 'rocket' ),
	'rocket_button',
	'rocket_cloudflare',
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