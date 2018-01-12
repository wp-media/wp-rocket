<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

// Are we white-labeled?
$rwl = rocket_is_white_label();

add_settings_section( 'rocket_display_cdn_options', __( 'Content Delivery Network options', 'rocket' ), '__return_false', 'rocket_cdn' );
$cloudflare_readonly = '';

if ( phpversion() < '5.4' ) {
	$cloudflare_readonly = '1';
}

/**
 * Cloudflare
 */
$rocket_do_cloudflare_settings = array();

if ( phpversion() < '5.4' ) {

	$rocket_do_cloudflare_settings[] = array(
		'type'        => 'helper_warning',
		'name'        => 'rocket_cloudflare_warning',
		'description' => __( 'Your PHP version is lower than 5.4. Cloudflare’s integration requires PHP 5.4 or greater and therefore is not available for you currently. We recommend you contact your web host in order to upgrade to the latest PHP version.', 'rocket' ),
	);
}

$rocket_do_cloudflare_settings[] = array(
	'type'         => 'checkbox',
	'label'        => __( 'Show Cloudflare settings tab', 'rocket' ),
	'label_for'    => 'do_cloudflare',
	'label_screen' => 'Cloudflare',
	'readonly'     => $cloudflare_readonly,
);

add_settings_field(
	'rocket_do_cloudflare',
	'Cloudflare',
	'rocket_field',
	'rocket_cdn',
	'rocket_display_cdn_options',
	$rocket_do_cloudflare_settings
);

/* Conditional panel caption if CF option is active */
if ( 0 !== absint( get_rocket_option( 'do_cloudflare' ) ) && ! $rwl ) {

	add_settings_field(
		'rocket_cdn_options_panel',
		false,
		'rocket_field',
		'rocket_cdn',
		'rocket_display_cdn_options',
		array(
			array(
				'type'        => 'helper_panel_description',
				'name'        => 'cdn_options_panel_caption',
				'description' => sprintf(
					'<span class="dashicons dashicons-cloud" aria-hidden="true"></span><strong>%1$s</strong>',
					/* translators: line-breaks recommended, but not mandatory; use URL of localised document if available in your language; %s = internal link to settings tab  */
					sprintf( __( 'Go to the <a href="%s">Cloudflare tab</a> to edit your Cloudflare settings. The CDN settings below do NOT apply to Cloudflare.<br>Read the documentation on <a href="http://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare" target="_blank">using WP Rocket with Cloudflare</a>.', 'rocket' ), '#tab_cloudflare' )
				),
			),
		)
	);
}

$rocket_cdn_options = array(
	array(
		'type'         => 'checkbox',
		'label'        => __( 'Enable Content Delivery Network', 'rocket' ),
		'label_for'    => 'cdn',
		'label_screen' => __( 'CDN:', 'rocket' ),
		/**
		 * Filters the value for the read only option of WP Rocket CDN
		 *
		 * @since 2.10.7
		 * @author Remy Perona
		 *
		 * @param bool $readonly true to disable the field, false otherwise.
		 */
		'readonly'     => apply_filters( 'rocket_readonly_cdn_option', false ),
	),
	array(
		'type'        => 'helper_description',
		'name'        => 'cdn',
		'description' => $rwl ?
		__( 'All URLs of static files (CSS, JS, images) will be rewritten to the CNAME(s) entered below.', 'rocket' ) :
		/* translators: line-break recommended, but not mandatory; use URL of localised document if available in your language  */
		__( 'All URLs of static files (CSS, JS, images) will be rewritten to the CNAME(s) entered below.<br>Read the documentation on <a href="http://docs.wp-rocket.me/article/42-using-wp-rocket-with-a-cdn" target="_blank">using WP Rocket with a CDN</a>.', 'rocket' ),
	),
);

// This filter is documented in inc/admin/ui/modules/cdn.php.
if ( apply_filters( 'rocket_readonly_cdn_option', false ) ) {
	$rocket_cdn_options[] = array(
		'type'        => 'helper_detection',
		'name'        => 'cdn_disabled',
		'description' => __( 'CDN is disabled because you are using WP Offload S3 and the assets addon to serve your images, CSS and JS files.', 'rocket' ),
	);
}

/**
 * CDN
 */
add_settings_field(
	'rocket_cdn',
	__( 'CDN:', 'rocket' ),
	'rocket_field',
	'rocket_cdn',
	'rocket_display_cdn_options',
	$rocket_cdn_options
);

/**
 * CDN CNAMES
 */
add_settings_field(
	'rocket_cdn_cnames',
	__( 'CDN CNAME(S):', 'rocket' ),
	'rocket_cnames_module',
	'rocket_cdn',
	'rocket_display_cdn_options'
);

/**
 * CDN with SSL
 */
add_settings_field(
	'rocket_cdn_on_ssl',
	__( 'CDN without SSL:', 'rocket' ),
	'rocket_field',
	'rocket_cdn',
	'rocket_display_cdn_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Disable CDN functionality on HTTPS pages', 'rocket' ),
			'label_for'    => 'cdn_ssl',
			'label_screen' => __( 'CDN without SSL:', 'rocket' ),
		),
		array(
			'type'        => 'helper_description',
			'description' => __( 'If your CDN account does not fully support SSL, you can disable URL rewriting on HTTPS pages here.', 'rocket' ),
		),
	)
);

add_settings_field(
	'rocket_cdn_reject_files',
	__( 'Exclude files:', 'rocket' ),
	'rocket_field',
	'rocket_cdn',
	'rocket_display_cdn_options',
	array(
		array(
			'type'        => 'helper_help',
			'name'        => 'cdn_reject_files',
			'description' => __( 'Specify URL(s) of files that should not get served via CDN (one per line)', 'rocket' ),
		),
		array(
			'type'         => 'textarea',
			'label_for'    => 'cdn_reject_files',
			'label_screen' => __( 'Exclude files:', 'rocket' ),
			'placeholder'  => '/wp-content/plugins/some-plugin/(.*).css',
		),
		array(
			'type'        => 'helper_description',
			'description' =>
			/* translators: line-break recommended; %s = code sample  */
			sprintf( __( 'The domain part of the URL will be stripped automatically.<br>Use %s wildcards to exclude all files of a given file type located at a specific path.', 'rocket' ), '<code>(.*)</code>' ),
		),
	)
);
