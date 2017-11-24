<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Add the CSS and JS files for WP Rocket options page
 *
 * @since 1.0.0
 */
function rocket_add_admin_css_js() {
	wp_enqueue_script( 'jquery-ui-sortable', null, array( 'jquery', 'jquery-ui-core' ), null, true );
	wp_enqueue_script( 'jquery-ui-draggable', null, array( 'jquery', 'jquery-ui-core' ), null, true );
	wp_enqueue_script( 'jquery-ui-droppable', null, array( 'jquery', 'jquery-ui-core' ), null, true );
	wp_enqueue_script( 'options-wp-rocket', WP_ROCKET_ADMIN_UI_JS_URL . 'options.js', array( 'jquery', 'jquery-ui-core' ), WP_ROCKET_VERSION, true );
	wp_enqueue_script( 'fancybox-wp-rocket', WP_ROCKET_ADMIN_UI_JS_URL . 'vendors/jquery.fancybox.pack.js', array( 'options-wp-rocket' ), WP_ROCKET_VERSION, true );
	wp_enqueue_script( 'sweet-alert-wp-rocket', WP_ROCKET_ADMIN_UI_JS_URL . 'vendors/sweetalert2.min.js', array( 'options-wp-rocket' ), WP_ROCKET_VERSION, true );

	wp_enqueue_style( 'options-wp-rocket', WP_ROCKET_ADMIN_UI_CSS_URL . 'options.css', array(), WP_ROCKET_VERSION );
	wp_enqueue_style( 'fancybox-wp-rocket', WP_ROCKET_ADMIN_UI_CSS_URL . 'fancybox/jquery.fancybox.css', array( 'options-wp-rocket' ), WP_ROCKET_VERSION );

	$minify_text = rocket_is_white_label()
		? __( 'This could break things!', 'rocket' ) . '<br><br>' .
		__( 'If you notice any errors on your website after having activated this setting, just deactivate it again, and your site will be back to normal.', 'rocket' )
		: __( 'This could break things!', 'rocket' ) . '<br>' .
		__( 'Before you continue activating this setting, read the documentation:', 'rocket' ) .
		sprintf(
			'<br><br><span class="dashicons dashicons-media-text" aria-hidden="true"></span>&#160;<a href="%1$s?utm_source=wp-rocket&utm_medium=wp-admin&utm_term=doc-minification&utm_campaign=plugin" target="_blank">%2$s</a><br/><br/>',
			/* translators: Documentation exists in EN, DE, FR, ES, IT */
			__( 'http://docs.wp-rocket.me/article/19-resolving-issues-with-file-optimization', 'rocket' ),
			/* translators: Translate doc title if you translated doc URL */
			__( 'Resolving issues with file optimization', 'rocket' )
		) .
		__( 'If you notice any errors on your website after having activated this setting, just deactivate it again, and your site will be back to normal.', 'rocket' );

	// Sweet Alert.
	$translation_array = array(
		'warningTitle'     => __( 'Fair warning:', 'rocket' ),
		'requiredTitle'    => __( 'All fields are required!', 'rocket' ),

		'cloudflareTitle'  => __( 'Cloudflare Settings', 'rocket' ),
		'cloudflareText'   => __( 'Save your changes to activate the Cloudflare tab.', 'rocket' ),

		'preloaderTitle' => __( 'Transmitting your message …', 'rocket' ),
		'preloaderImg'   => WP_ROCKET_ADMIN_UI_IMG_URL . 'preloader.gif',

		'badServerConnectionTitle'             => __( 'Unable to transmit', 'rocket' ),
		'badServerConnectionText'              => __( 'It seems that communications with Mission Control are temporarily down … Please send a support ticket while our Rocketeers fix the issue.', 'rocket' ),
		'badServerConnectionConfirmButtonText' => __( 'Get help from a Support Rocketeer', 'rocket' ),

		'warningSupportTitle' => __( 'Last steps before contacting us', 'rocket' ),
		// translators: %s is the documentation URL.
		'warningSupportText'  => sprintf( __( 'You have to read the <a href="%s" target="_blank">documentation</a> and to agree to send informations relative to your website to submit a support ticket.', 'rocket' ), get_rocket_documentation_url() . '?utm_source=wp-rocket&utm_medium=wp-admin&utm_term=doc-support&utm_campaign=plugin' ),

		'successSupportTitle' => __( 'Transmission Received!', 'rocket' ),
		'successSupportText'  => __( 'We have received your ticket and will reply back within a few hours!', 'rocket' ) . '<br/>' . __( 'We answer every ticket, so please check your spam folder if you do not hear from us.', 'rocket' ),

		'badSupportTitle'      => __( 'Hmm, that’s problematic …', 'rocket' ),
		'badSupportText'       => __( 'It looks like you have got yourself a “nulled” version of WP Rocket. In order to activate your plugin and receive support and updates you will need a valid license.', 'rocket' ) . '<br/>' . __( 'Click below to purchase a license with a 20% discount automatically applied.', 'rocket' ),
		'badConfirmButtonText' => __( 'Buy a valid license now', 'rocket' ),

		'expiredSupportTitle'      => __( 'Uh-oh, you’re out of fuel …', 'rocket' ),
		'expiredSupportText'       => __( 'To keep WP Rocket running with access to support and updates, <strong>you will need to renew your license</strong>.', 'rocket' ) . '<br/><br/>' . __( 'Click below to renew with a <strong>discount of 50%</strong> automatically applied!', 'rocket' ),
		'expiredConfirmButtonText' => __( 'Buy renewal now', 'rocket' ),

		'minifyText' => $minify_text,

		'confirmButtonText' => __( 'Activate this setting', 'rocket' ),
		'cancelButtonText'  => __( 'Cancel', 'rocket' ),
	);
	wp_localize_script( 'options-wp-rocket', 'sawpr', $translation_array );
	wp_enqueue_style( 'sweet-alert-wp-rocket', WP_ROCKET_ADMIN_UI_CSS_URL . 'sweetalert2.min.css', array( 'options-wp-rocket' ), WP_ROCKET_VERSION );
}
add_action( 'admin_print_styles-settings_page_' . WP_ROCKET_PLUGIN_SLUG, 'rocket_add_admin_css_js' );

/**
 * Add the CSS and JS files needed by WP Rocket everywhere on admin pages
 *
 * @since 2.1
 */
function rocket_add_admin_css_js_everywhere() {
	wp_enqueue_script( 'all-wp-rocket', WP_ROCKET_ADMIN_UI_JS_URL . 'all.js', array( 'jquery' ), WP_ROCKET_VERSION, true );
	wp_enqueue_style( 'admin-wp-rocket', WP_ROCKET_ADMIN_UI_CSS_URL . 'admin.css', array(), WP_ROCKET_VERSION );
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
		property_blacklist: ['$initial_referrer', '$current_url', '$initial_referring_domain', '$referrer', '$referring_domain']
	} );</script><!-- end Mixpanel -->
	<script>
		mixpanel.track( 'WP Rocket', <?php echo wp_json_encode( rocket_analytics_data() ); ?> );
	</script>
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
	wp_enqueue_style( 'plugin-install' );

	wp_enqueue_script( 'plugin-install' );
	wp_enqueue_script( 'updates' );
	add_thickbox();
}
add_action( 'admin_print_styles-media-new.php', 'rocket_enqueue_modal_plugin' );
add_action( 'admin_print_styles-upload.php', 'rocket_enqueue_modal_plugin' );
add_action( 'admin_print_styles-settings_page_' . WP_ROCKET_PLUGIN_SLUG, 'rocket_enqueue_modal_plugin' );
