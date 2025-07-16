<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\TrackingPlugin as MixpanelTracking;

class Tracking extends Abstract_Render {
	/**
	 * Options Data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Optin instance.
	 *
	 * @var Optin
	 */
	private $optin;

	/**
	 * Mixpanel Tracking instance.
	 *
	 * @var MixpanelTracking
	 */
	private $mixpanel;

	/**
	 * Constructor.
	 *
	 * @param Options_Data     $options Options Data instance.
	 * @param Optin            $optin Optin instance.
	 * @param MixpanelTracking $mixpanel Mixpanel Tracking instance.
	 * @param string           $template_path Path to the template files.
	 */
	public function __construct( Options_Data $options, Optin $optin, MixpanelTracking $mixpanel, $template_path ) {
		parent::__construct( $template_path );

		$this->options  = $options;
		$this->optin    = $optin;
		$this->mixpanel = $mixpanel;

		$this->mixpanel->identify( $this->options->get( 'consumer_email', '' ) );
	}

	/**
	 * Track option change.
	 *
	 * @param mixed $old_value The old value of the option.
	 * @param mixed $value     The new value of the option.
	 */
	public function track_option_change( $old_value, $value ) {
		if ( ! $this->optin->is_enabled() ) {
			return;
		}

		/*
		 * Filters the tracked options.
		 *
		 * @since 3.19.2
		 *
		 * @param string[] $options Array of options that are tracked by default.
		 * @return string[] array of strings.
		 */
		$options_to_track = wpm_apply_filters_typed(
			'string[]',
			'rocket_mixpanel_tracked_options',
			[]
		);

		foreach ( $options_to_track as $option_tracked ) {
			if ( ! isset( $old_value[ $option_tracked ], $value[ $option_tracked ] ) ) {
				continue;
			}

			if ( $old_value[ $option_tracked ] === $value[ $option_tracked ] ) {
				continue;
			}

			$this->mixpanel->track(
				'WPM Option Changed',
				[
					'brand'          => 'WP Media',
					'product'        => 'WP Rocket',
					'context'        => 'wp_plugin',
					'option_name'    => $option_tracked,
					'previous_value' => $old_value[ $option_tracked ],
					'new_value'      => $value[ $option_tracked ],
				]
			);
		}
	}

	/**
	 * Migrate opt-in to new package on upgrade
	 *
	 * @param string $new_version The new version of the plugin.
	 * @param string $old_version The old version of the plugin.
	 *
	 * @return void
	 */
	public function migrate_optin( string $new_version, string $old_version ): void {
		if ( version_compare( $old_version, '3.19.1', '>=' ) ) {
			return;
		}

		if ( ! $this->options->get( 'analytics_enabled', false ) ) {
			return;
		}

		$this->optin->enable();
	}

	/**
	 * Render the opt-in section.
	 *
	 * @return void
	 */
	public function render_optin(): void {
		echo $this->generate( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'optin',
			[
				'current_value' => (int) $this->optin->is_enabled(),
			]
		);
	}

	/**
	 * Handle AJAX request to toggle opt-in.
	 *
	 * @return void
	 */
	public function ajax_toggle_optin(): void {
		check_ajax_referer( 'rocket-ajax' );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_send_json_error( 'Missing capability' );
		}

		if ( ! isset( $_POST['value'] ) ) {
			wp_send_json_error( 'Missing value parameter' );
		}

		$value = sanitize_key( wp_unslash( $_POST['value'] ) );

		if ( '1' === $value ) {
			$this->optin->enable();
			wp_send_json_success( 'Opt-in enabled.' );
		} elseif ( '0' === $value ) {
			$this->optin->disable();
			wp_send_json_success( 'Opt-in disabled.' );
		}

		wp_send_json_error( 'Invalid value parameter.' );
	}

	/**
	 * Add opt-in status to admin scripts.
	 *
	 * @return void
	 */
	public function localize_optin_status(): void {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		// Get the license email and hash it for privacy.
		$consumer_email = $this->options->get( 'consumer_email', '' );
		$hashed_email   = ! empty( $consumer_email ) ? $this->mixpanel->hash( $consumer_email ) : '';

		wp_localize_script(
			'wpr-admin-common',
			'rocket_mixpanel_data',
			[
				'optin_enabled' => $this->optin->is_enabled() ? true : false,
				'brand'         => 'WP Media',
				'product'       => 'WP Rocket',
				'context'       => 'wp_plugin',
				'path'          => isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '',
				'user_id'       => $hashed_email,
			]
		);
	}

	/**
	 * Injects Mixpanel JavaScript SDK when opt-in is enabled.
	 *
	 * @since 3.19.2
	 * @return void
	 */
	public function inject_mixpanel_script(): void {
		// Only inject if user has capability and opt-in is enabled.
		if ( ! current_user_can( 'rocket_manage_options' ) || ! $this->optin->is_enabled() ) {
			return;
		}

		?>
		<!-- start Mixpanel --><script type="text/javascript">const MIXPANEL_CUSTOM_LIB_URL = "<?php echo esc_js( rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'mixpanel-2-latest.min.js' ); ?>";(function(e,a){if(!a.__SV){var b=window;try{var c,l,i,j=b.location,g=j.hash;c=function(a,b){return(l=a.match(RegExp(b+"=([^&]*)")))?l[1]:null};g&&c(g,"state")&&(i=JSON.parse(decodeURIComponent(c(g,"state"))),"mpeditor"===i.action&&(b.sessionStorage.setItem("_mpcehash",g),history.replaceState(i.desiredHash||"",e.title,j.pathname+j.search)))}catch(m){}var k,h;window.mixpanel=a;a._i=[];a.init=function(b,c,f){function e(b,a){var c=a.split(".");2==c.length&&(b=b[c[0]],a=c[1]);b[a]=function(){b.push([a].concat(Array.prototype.slice.call(arguments,
0)))}}var d=a;"undefined"!==typeof f?d=a[f]=[]:f="mixpanel";d.people=d.people||[];d.toString=function(b){var a="mixpanel";"mixpanel"!==f&&(a+="."+f);b||(a+=" (stub)");return a};d.people.toString=function(){return d.toString(1)+".people (stub)"};k="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config reset people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");
for(h=0;h<k.length;h++)e(d,k[h]);a._i.push([b,c,f])};a.__SV=1.2;b=e.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";c=e.getElementsByTagName("script")[0];c.parentNode.insertBefore(b,c)}})(document,window.mixpanel||[]);
		mixpanel.init("<?php echo $this->mixpanel->get_token(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>", {
			'ip':false,
			'property_blacklist': ['$initial_referrer', '$current_url', '$initial_referring_domain', '$referrer', '$referring_domain']
		} );
		</script><!-- end Mixpanel -->
		<?php
	}
}
