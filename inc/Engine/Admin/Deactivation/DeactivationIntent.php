<?php

namespace WP_Rocket\Engine\Admin\Deactivation;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Interfaces\Render_Interface;

/**
 * Deactivation intent form on plugins page
 *
 * @since 3.0
 */
class DeactivationIntent implements Subscriber_Interface {
	/**
	 * Render Interface
	 *
	 * @since 3.0
	 *
	 * @var Render_Interface
	 */
	private $render;

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Options_Data instance.
	 *
	 * @since 3.0
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 *
	 * @param Render_Interface $render      Render interface.
	 * @param Options          $options_api Options instance.
	 * @param Options_Data     $options     Options_Data instance.
	 */
	public function __construct( Render_Interface $render, Options $options_api, Options_Data $options ) {
		$this->render      = $render;
		$this->options_api = $options_api;
		$this->options     = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.0
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_print_footer_scripts-plugins.php' => 'insert_mixpanel_tracking',
			'admin_footer'                           => 'insert_deactivation_intent_form',
			'wp_ajax_rocket_safe_mode'               => 'activate_safe_mode',
		];
	}

	/**
	 * Inserts mixpanel tracking on plugins page to send deactivation data
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function insert_mixpanel_tracking() {
		?>
		<!-- start Mixpanel --><script type="text/javascript">(function(e,a){if(!a.__SV){var b=window;try{var c,l,i,j=b.location,g=j.hash;c=function(a,b){return(l=a.match(RegExp(b+"=([^&]*)")))?l[1]:null};g&&c(g,"state")&&(i=JSON.parse(decodeURIComponent(c(g,"state"))),"mpeditor"===i.action&&(b.sessionStorage.setItem("_mpcehash",g),history.replaceState(i.desiredHash||"",e.title,j.pathname+j.search)))}catch(m){}var k,h;window.mixpanel=a;a._i=[];a.init=function(b,c,f){function e(b,a){var c=a.split(".");2==c.length&&(b=b[c[0]],a=c[1]);b[a]=function(){b.push([a].concat(Array.prototype.slice.call(arguments,
0)))}}var d=a;"undefined"!==typeof f?d=a[f]=[]:f="mixpanel";d.people=d.people||[];d.toString=function(b){var a="mixpanel";"mixpanel"!==f&&(a+="."+f);b||(a+=" (stub)");return a};d.people.toString=function(){return d.toString(1)+".people (stub)"};k="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config reset people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");
for(h=0;h<k.length;h++)e(d,k[h]);a._i.push([b,c,f])};a.__SV=1.2;b=e.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";c=e.getElementsByTagName("script")[0];c.parentNode.insertBefore(b,c)}})(document,window.mixpanel||[]);

mixpanel.init("a36067b00a263cce0299cfd960e26ecf", {
		'ip':false,
		property_blacklist: ['$initial_referrer', '$current_url', '$initial_referring_domain', '$referrer', '$referring_domain']
	} );

		mixpanel.track_links( '#mixpanel-send-deactivation', 'Deactivation Intent', function(ele) {
			return {
				'Reason': document.getElementById('wpr-reason').value,
				'Details': document.getElementById('wpr-details').value,
			}
		} );
		</script><!-- end Mixpanel -->
		<?php
	}

	/**
	 * Inserts the deactivation intent form on plugins page
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function insert_deactivation_intent_form() {
		$current_screen = get_current_screen();

		if ( 'plugins' !== $current_screen->id && 'plugins-network' !== $current_screen->id ) {
			return;
		}

		$this->render->render_form();
	}

	/**
	 * Activates WP Rocket safe mode by deactivating possibly layout breaking options
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function activate_safe_mode() {
		check_ajax_referer( 'rocket-ajax', 'nonce' );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_send_json_error();
			return;
		}

		/**
		 * Filters the array of options to reset when activating safe mode
		 *
		 * @since 3.7
		 *
		 * @param array $options Array of options to reset.
		 */
		$reset_options = apply_filters(
			'rocket_safe_mode_reset_options',
			[
				'embeds'                 => 0,
				'async_css'              => 0,
				'lazyload'               => 0,
				'lazyload_iframes'       => 0,
				'lazyload_youtube'       => 0,
				'minify_css'             => 0,
				'minify_concatenate_css' => 0,
				'minify_js'              => 0,
				'minify_concatenate_js'  => 0,
				'defer_all_js'           => 0,
				'delay_js'               => 0,
				'remove_unused_css'      => 0,
				'minify_google_fonts'    => 0,
				'cdn'                    => 0,
			]
		);

		$this->options->set_values( $reset_options );
		$this->options_api->set( 'settings', $this->options->get_options() );

		wp_send_json_success();
	}
}
