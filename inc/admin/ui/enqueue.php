<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Add the CSS and JS files for WP Rocket options page
 *
 * @since 1.0.0
 */
add_action( 'admin_print_styles-settings_page_' . WP_ROCKET_PLUGIN_SLUG, '__rocket_add_admin_css_js' );
function __rocket_add_admin_css_js()
{
	wp_enqueue_script( 'jquery-ui-sortable', null, array( 'jquery', 'jquery-ui-core' ), null, true );
	wp_enqueue_script( 'jquery-ui-draggable', null, array( 'jquery', 'jquery-ui-core' ), null, true );
	wp_enqueue_script( 'jquery-ui-droppable', null, array( 'jquery', 'jquery-ui-core' ), null, true );
	wp_enqueue_script( 'options-wp-rocket', WP_ROCKET_ADMIN_JS_URL . 'options.js', array( 'jquery', 'jquery-ui-core' ), WP_ROCKET_VERSION, true );
	wp_enqueue_script( 'fancybox-wp-rocket', WP_ROCKET_ADMIN_JS_URL . 'vendors/jquery.fancybox.pack.js', array( 'options-wp-rocket' ), WP_ROCKET_VERSION, true );
	wp_enqueue_script( 'sweet-alert-wp-rocket', WP_ROCKET_ADMIN_JS_URL . 'vendors/sweet-alert.min.js', array( 'options-wp-rocket' ), WP_ROCKET_VERSION, true );

	wp_enqueue_style( 'options-wp-rocket', WP_ROCKET_ADMIN_CSS_URL . 'options.css', array(), WP_ROCKET_VERSION );
	wp_enqueue_style( 'fancybox-wp-rocket', WP_ROCKET_ADMIN_CSS_URL . 'fancybox/jquery.fancybox.css', array( 'options-wp-rocket' ), WP_ROCKET_VERSION );

	// Sweet Alert
	$translation_array = array(
		'warning_title'  	 => __( 'Are you sure?', 'rocket' ),
		'cloudflare_title'   => __( 'CloudFlare Settings', 'rocket' ),
		'minify_text'  		 => __( 'In case of any display errors we recommend following our documentation: ', 'rocket' ) . ' <a href="http://docs.wp-rocket.me/article/19-resolving-issues-with-minification/?utm_source=wp-rocket&utm_medium=wp-admin&utm_term=doc-minification&utm_campaign=plugin">Resolving Issues with Minification</a>.<br/><br/>' . sprintf(  __( 'You can also <a href="%s">contact our support</a> if you need help implementing that.', 'rocket' ), 'http://wp-rocket.me/support/?utm_source=wp-rocket&utm_medium=wp-admin&utm_term=support-minification&utm_campaign=plugin' ),
		'cloudflare_text'    => __( 'Click "Save Changes" to activate the Cloudflare tab.', 'rocket' ),
		'confirmButtonText'  => __( 'Yes, I\'m sure!', 'rocket' ),
		'cancelButtonText' 	 => __( 'Cancel', 'rocket' )
	);
	wp_localize_script( 'options-wp-rocket', 'sawpr', $translation_array );
	wp_enqueue_style( 'sweet-alert-wp-rocket', WP_ROCKET_ADMIN_CSS_URL . 'sweet-alert.css', array( 'options-wp-rocket' ), WP_ROCKET_VERSION );
}

/**
 * Add the CSS and JS files needed by WP Rocket everywhere on admin pages
 *
 * @since 2.1
 */
add_action( 'admin_print_styles', '__rocket_add_admin_css_js_everywhere', 11 );
function __rocket_add_admin_css_js_everywhere()
{
	wp_enqueue_script( 'all-wp-rocket', WP_ROCKET_ADMIN_JS_URL . 'all.js', array( 'jquery' ), WP_ROCKET_VERSION, true );
}

/**
 * Add some CSS to display the dismiss cross
 *
 * @since 1.1.10
 *
 */
add_action( 'admin_print_styles', '__rocket_admin_print_styles' );
function __rocket_admin_print_styles()
{
	wp_enqueue_style( 'admin-wp-rocket', WP_ROCKET_ADMIN_CSS_URL . 'admin.css', array(), WP_ROCKET_VERSION );
}