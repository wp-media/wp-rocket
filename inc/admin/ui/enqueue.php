<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Add the CSS and JS files for WP Rocket options page
 *
 * @since 1.0.0
 */
function rocket_add_admin_css_js() {
	wp_enqueue_style( 'wpr-admin', WP_ROCKET_ASSETS_CSS_URL . 'wpr-admin.css', null, WP_ROCKET_VERSION );
	wp_enqueue_script( 'wpr-admin', WP_ROCKET_ASSETS_JS_URL . 'wpr-admin.js', null, WP_ROCKET_VERSION, true );
	wp_enqueue_script( 'wpr-ads', WP_ROCKET_ASSETS_JS_URL . 'ads.js', null, WP_ROCKET_VERSION, true );
	wp_localize_script( 'wpr-admin', 'rocket_ajax_data', array( 'nonce' => wp_create_nonce( 'rocket-ajax' ) ) );

	if ( is_rtl() ) {
		wp_enqueue_style( 'wpr-admin-rtl', WP_ROCKET_ASSETS_CSS_URL . 'wpr-admin-rtl.css', null, WP_ROCKET_VERSION );
	}

}
add_action( 'admin_print_styles-settings_page_' . WP_ROCKET_PLUGIN_SLUG, 'rocket_add_admin_css_js' );

/**
 * Add the CSS and JS files needed by WP Rocket everywhere on admin pages
 *
 * @since 2.1
 *
 * @param string $hook Current admin page.
 */
function rocket_add_admin_css_js_everywhere( $hook ) {
	wp_enqueue_script( 'wpr-admin-common', WP_ROCKET_ASSETS_JS_URL . 'wpr-admin-common.js', array( 'jquery' ), WP_ROCKET_VERSION, true );
	wp_enqueue_style( 'wpr-admin-common', WP_ROCKET_ASSETS_CSS_URL . 'wpr-admin-common.css', array(), WP_ROCKET_VERSION );

	if ( 'plugins.php' === $hook ) {
		wp_enqueue_style( 'wpr-modal', WP_ROCKET_ASSETS_CSS_URL . 'wpr-modal.css', null, WP_ROCKET_VERSION );
		wp_enqueue_script( 'wpr-modal', WP_ROCKET_ASSETS_JS_URL . 'wpr-modal.js', null, WP_ROCKET_VERSION, true );
		wp_localize_script( 'wpr-modal', 'rocket_ajax_data', array( 'nonce' => wp_create_nonce( 'rocket-ajax' ) ) );
	}
}
add_action( 'admin_enqueue_scripts', 'rocket_add_admin_css_js_everywhere', 11 );

/**
 * Adds mixpanel JS code in header when analytics data should be sent
 *
 * @since 2.11
 * @author Remy Perona
 */
function rocket_add_mixpanel_code() {
	if ( rocket_send_analytics_data() ) {
	?>
	<!-- start Mixpanel --><script type="text/javascript">(function(e,a){if(!a.__SV){var b=window;try{var c,l,i,j=b.location,g=j.hash;c=function(a,b){return(l=a.match(RegExp(b+"=([^&]*)")))?l[1]:null};g&&c(g,"state")&&(i=JSON.parse(decodeURIComponent(c(g,"state"))),"mpeditor"===i.action&&(b.sessionStorage.setItem("_mpcehash",g),history.replaceState(i.desiredHash||"",e.title,j.pathname+j.search)))}catch(m){}var k,h;window.mixpanel=a;a._i=[];a.init=function(b,c,f){function e(b,a){var c=a.split(".");2==c.length&&(b=b[c[0]],a=c[1]);b[a]=function(){b.push([a].concat(Array.prototype.slice.call(arguments,
0)))}}var d=a;"undefined"!==typeof f?d=a[f]=[]:f="mixpanel";d.people=d.people||[];d.toString=function(b){var a="mixpanel";"mixpanel"!==f&&(a+="."+f);b||(a+=" (stub)");return a};d.people.toString=function(){return d.toString(1)+".people (stub)"};k="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config reset people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");
for(h=0;h<k.length;h++)e(d,k[h]);a._i.push([b,c,f])};a.__SV=1.2;b=e.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";c=e.getElementsByTagName("script")[0];c.parentNode.insertBefore(b,c)}})(document,window.mixpanel||[]);
	mixpanel.init("a36067b00a263cce0299cfd960e26ecf", {
		'ip':false,
		'property_blacklist': ['$initial_referrer', '$current_url', '$initial_referring_domain', '$referrer', '$referring_domain']
	} );
	mixpanel.track( 'WP Rocket', <?php echo wp_json_encode( rocket_analytics_data() ); ?> );
	mixpanel.track( 'Settings Sidebar Display', localStorage.getItem('wpr-show-sidebar') );
	</script><!-- end Mixpanel -->
	<?php
	}
}
add_action( 'admin_print_scripts', 'rocket_add_mixpanel_code' );

/**
 * Add CSS & JS files for the Imagify installation call to action
 *
 * @since 2.7
 */
function rocket_enqueue_modal_plugin() {
	$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

	if ( defined( 'IMAGIFY_VERSION' ) || in_array( 'rocket_imagify_notice', (array) $boxes, true ) || 1 === get_option( 'wp_rocket_dismiss_imagify_notice' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_enqueue_style( 'plugin-install' );

	wp_enqueue_script( 'plugin-install' );
	wp_enqueue_script( 'updates' );
	add_thickbox();
}
add_action( 'admin_print_styles-media-new.php', 'rocket_enqueue_modal_plugin' );
add_action( 'admin_print_styles-upload.php', 'rocket_enqueue_modal_plugin' );
add_action( 'admin_print_styles-settings_page_' . WP_ROCKET_PLUGIN_SLUG, 'rocket_enqueue_modal_plugin' );
