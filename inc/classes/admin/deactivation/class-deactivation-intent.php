<?php
namespace WP_Rocket\Admin\Deactivation;

/**
 * Deactivation intent form on plugins page
 *
 * @since 3.0
 * @author Remy Perona
 */
class Deactivation_Intent {
	/**
	 * Render Interface
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var \WP_Rocket\Interfaces\Render_Interface
	 */
	private $render;

	/**
	 * Options instance.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var \WP_Rocket\Admin\Options
	 */
	private $options_api;

	/**
	 * Options_Data instance.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var \WP_Rocket\Admin\Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param \WP_Rocket\Interfaces\Render_Interface $render      Render interface.
	 * @param \WP_Rocket\Admin\Options               $options_api Options instance.
	 * @param \WP_Rocket\Admin\Options_Data          $options     Options_Data instance.
	 */
	public function __construct( \WP_Rocket\Interfaces\Render_Interface $render, \WP_Rocket\Admin\Options $options_api, \WP_Rocket\Admin\Options_Data $options ) {
		$this->render      = $render;
		$this->options_api = $options_api;
		$this->options     = $options;
	}

	/**
	 * Custom constructor
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param \WP_Rocket\Interfaces\Render_Interface $render      Render interface.
	 * @param \WP_Rocket\Admin\Options               $options_api Options instance.
	 * @param \WP_Rocket\Admin\Options_Data          $options     Options_Data instance.
	 *
	 * @return void
	 */
	public static function load( \WP_Rocket\Interfaces\Render_Interface $render, \WP_Rocket\Admin\Options $options_api, \WP_Rocket\Admin\Options_Data $options ) {
		$self = new self( $render, $options_api, $options );

		add_action( 'admin_print_footer_scripts-plugins.php', [ $self, 'insert_mixpanel_tracking' ] );
		add_action( 'admin_footer', [ $self, 'insert_deactivation_intent_form' ] );
		add_action( 'wp_ajax_rocket_safe_mode', [ $self, 'activate_safe_mode' ] );
	}

	/**
	 * Inserts mixpanel tracking on plugins page to send deactivation data
	 *
	 * @since 3.0
	 * @author Remy Perona
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
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function insert_deactivation_intent_form() {
		$current_screen = get_current_screen();

		if ( 'plugins' !== $current_screen->id ) {
			return;
		}

		$this->render->render_form();
	}

	/**
	 * Activates WP Rocket safe mode by deactivating possibly layout breaking options
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function activate_safe_mode() {
		check_ajax_referer( 'rocket-ajax' );

		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			wp_die();
		}

		$reset_options = [
			'embeds'                 => 0,
			'defer_all_js'           => 0,
			'async_css'              => 0,
			'lazyload'               => 0,
			'lazyload_iframes'       => 0,
			'lazyload_youtube'       => 0,
			'minify_css'             => 0,
			'minify_concatenate_css' => 0,
			'minify_js'              => 0,
			'minify_concatenate_js'  => 0,
			'minify_html'            => 0,
			'minify_google_fonts'    => 0,
			'remove_query_strings'   => 0,
			'cdn'                    => 0,
		];

		$this->options->set_values( $reset_options );
		$this->options_api->set( 'settings', $this->options->get_options() );

		return wp_send_json_success();
	}
}
