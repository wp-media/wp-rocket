<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Add the CSS and JS files for WP Rocket options page
 *
 * @since 1.0.0
 */
add_action( 'admin_print_styles-settings_page_' . WP_ROCKET_PLUGIN_SLUG, '__rocket_add_admin_css_js' );
function __rocket_add_admin_css_js() {
	wp_enqueue_script( 'jquery-ui-sortable', null, array( 'jquery', 'jquery-ui-core' ), null, true );
	wp_enqueue_script( 'jquery-ui-draggable', null, array( 'jquery', 'jquery-ui-core' ), null, true );
	wp_enqueue_script( 'jquery-ui-droppable', null, array( 'jquery', 'jquery-ui-core' ), null, true );
	wp_enqueue_script( 'options-wp-rocket', WP_ROCKET_ADMIN_UI_JS_URL . 'options.js', array( 'jquery', 'jquery-ui-core' ), WP_ROCKET_VERSION, true );
	wp_enqueue_script( 'fancybox-wp-rocket', WP_ROCKET_ADMIN_UI_JS_URL . 'vendors/jquery.fancybox.pack.js', array( 'options-wp-rocket' ), WP_ROCKET_VERSION, true );
	wp_enqueue_script( 'sweet-alert-wp-rocket', WP_ROCKET_ADMIN_UI_JS_URL . 'vendors/sweetalert2.min.js', array( 'options-wp-rocket' ), WP_ROCKET_VERSION, true );

	wp_enqueue_style( 'options-wp-rocket', WP_ROCKET_ADMIN_UI_CSS_URL . 'options.css', array(), WP_ROCKET_VERSION );
	wp_enqueue_style( 'fancybox-wp-rocket', WP_ROCKET_ADMIN_UI_CSS_URL . 'fancybox/jquery.fancybox.css', array( 'options-wp-rocket' ), WP_ROCKET_VERSION );

    $minify_text = rocket_is_white_label() ? __( 'In case of any display errors we recommend to disable the option.', 'rocket' ) : __( 'In case of any display errors we recommend following our documentation: ', 'rocket' ) . ' <a href="http://docs.wp-rocket.me/article/19-resolving-issues-with-minification/?utm_source=wp-rocket&utm_medium=wp-admin&utm_term=doc-minification&utm_campaign=plugin">Resolving Issues with Minification</a>.<br/><br/>' . sprintf(  __( 'You can also <a href="%s">contact our support</a> if you need help implementing that.', 'rocket' ), 'http://wp-rocket.me/support/?utm_source=wp-rocket&utm_medium=wp-admin&utm_term=support-minification&utm_campaign=plugin' );

	// Sweet Alert
	$translation_array = array(
		'warningTitle'     => __( 'Are you sure?', 'rocket' ),
		'requiredTitle'    => __( 'All fields are required!', 'rocket' ),
		
		'cloudflareTitle'  => __( 'CloudFlare Settings', 'rocket' ),
		'cloudflareText'   => __( 'Click "Save Changes" to activate the Cloudflare tab.', 'rocket' ),

		'preloaderTitle' => __( 'Transmitting across the galaxy...', 'rocket' ),
		'preloaderImg'	 => WP_ROCKET_ADMIN_UI_IMG_URL . 'preloader.gif',

		'badServerConnectionTitle'             => __( 'Unable to transmit', 'rocket' ),
		'badServerConnectionText'              => __( 'It seems that communications with Mission Control are temporarily down....please submit a support ticket while our Rocket Scientists fix the issue.', 'rocket' ),
		'badServerConnectionConfirmButtonText' => __( 'Get help from a rocket scientist', 'rocket' ),

		'warningSupportTitle' => __( 'Last steps before contacting us', 'rocket' ),
		'warningSupportText'  => sprintf( __( 'You have to read the <a href="%s" target="_blank">documentation</a> and to agree to send informations relative to your website to submit a support ticket.', 'rocket' ), get_rocket_documentation_url() . '?utm_source=wp-rocket&utm_medium=wp-admin&utm_term=doc-support&utm_campaign=plugin' ),

		'successSupportTitle' => __( 'Transmission Received!', 'rocket' ),
		'successSupportText'  => __( 'We\'ve received your ticket and will reply back within a few hours!', 'rocket' ) . '<br/>' . __( 'We answer every ticket so check your spam if you don\'t hear from us.', 'rocket' ),

		'badSupportTitle'      => __( 'Oh dear, someone\'s been naughty...', 'rocket' ),
		'badSupportText'       => __( 'Well, well, looks like you\'ve got yourself a "nulled" version! We don\'t provide support to hackers or pirates, so you will need a valid license to proceed.', 'rocket' ) . '<br/>' . __( 'Click below to buy a license with a 20% discount automatically applied.', 'rocket' ),
		'badConfirmButtonText' => __( 'Buy It Now!', 'rocket' ),

		'expiredSupportTitle'      => __( 'Uh-oh, you\'re out of fuel!', 'rocket' ),
		'expiredSupportText'       => __( 'To keep your Rocket running with access to support, <strong>you\'ll need to renew your license</strong>.', 'rocket' ) . '<br/><br/>' .  __( 'Click below to renew with a <strong>discount of 50%</strong> automatically applied!', 'rocket' ),
		'expiredConfirmButtonText' => __( 'I re-synchronize now!', 'rocket' ),

		'minifyText' => $minify_text,

		'confirmButtonText' => __( 'Yes, I\'m sure!', 'rocket' ),
		'cancelButtonText'  => __( 'Cancel', 'rocket' )
	);
	wp_localize_script( 'options-wp-rocket', 'sawpr', $translation_array );
	wp_enqueue_style( 'sweet-alert-wp-rocket', WP_ROCKET_ADMIN_UI_CSS_URL . 'sweetalert2.min.css', array( 'options-wp-rocket' ), WP_ROCKET_VERSION );
}

/**
 * Add the CSS and JS files needed by WP Rocket everywhere on admin pages
 *
 * @since 2.1
 */
add_action( 'admin_print_styles', '__rocket_add_admin_css_js_everywhere', 11 );
function __rocket_add_admin_css_js_everywhere() {
	wp_enqueue_script( 'all-wp-rocket', WP_ROCKET_ADMIN_UI_JS_URL . 'all.js', array( 'jquery' ), WP_ROCKET_VERSION, true );
}

/**
 * Add some CSS to display the dismiss cross
 *
 * @since 1.1.10
 *
 */
add_action( 'admin_print_styles', '__rocket_admin_print_styles' );
function __rocket_admin_print_styles() {
	wp_enqueue_style( 'admin-wp-rocket', WP_ROCKET_ADMIN_UI_CSS_URL . 'admin.css', array(), WP_ROCKET_VERSION );
}


/**
 * Add CSS & JS files for the Imagify installation call to action
 *
 * @since 2.7
 */
add_action( 'admin_print_styles-media-new.php', '__rocket_enqueue_modal_plugin' );
add_action( 'admin_print_styles-upload.php', '__rocket_enqueue_modal_plugin' );
add_action( 'admin_print_styles-settings_page_' . WP_ROCKET_PLUGIN_SLUG, '__rocket_enqueue_modal_plugin' );
function __rocket_enqueue_modal_plugin() {
    wp_enqueue_style( 'thickbox' );
    wp_enqueue_style( 'plugin-install' );
    
    wp_enqueue_script( 'plugin-install' );
    wp_enqueue_script( 'tgm-modal-wp-rocket', WP_ROCKET_ADMIN_UI_JS_URL . 'vendors/tgm-modal.min.js', array( 'jquery' ), WP_ROCKET_VERSION, true );
}