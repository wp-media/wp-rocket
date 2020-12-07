<?php

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Admin\Options_Data;

class Settings {
	/**
	 * Array of defaults scripts to delay
	 *
	 * @var array
	 */
	private $defaults = [
		'getbutton.io',
		'//a.omappapi.com/app/js/api.min.js',
		'feedbackcompany.com/includes/widgets/feedback-company-widget.min.js',
		'snap.licdn.com/li.lms-analytics/insight.min.js',
		'static.ads-twitter.com/uwt.js',
		'platform.twitter.com/widgets.js',
		'twq(',
		'/sdk.js#xfbml',
		'static.leadpages.net/leadbars/current/embed.js',
		'translate.google.com/translate_a/element.js',
		'widget.manychat.com',
		'xfbml.customerchat.js',
		'static.hotjar.com/c/hotjar-',
		'smartsuppchat.com/loader.js',
		'grecaptcha.execute',
		'Tawk_API',
		'shareaholic',
		'sharethis',
		'simple-share-buttons-adder',
		'addtoany',
		'font-awesome',
		'wpdiscuz',
		'cookie-law-info',
		'pinit.js',
		'/gtag/js',
		'gtag(',
		'/gtm.js',
		'/gtm-',
		'fbevents.js',
		'fbq(',
		'google-analytics.com/analytics.js',
		'ga( \'',
		'ga(\'',
		'adsbygoogle.js',
		'ShopifyBuy',
		'widget.trustpilot.com/bootstrap',
		'ft.sdk.min.js',
		'apps.elfsight.com/p/platform.js',
		'livechatinc.com/tracking.js',
		'LiveChatWidget',
		'/busting/facebook-tracking/',
		'olark',
		'pixel-caffeine/build/frontend.js',
	];

	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Add the delay JS options to the WP Rocket options array
	 *
	 * @since 3.7
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_options( $options ) {
		$options = (array) $options;

		$options['delay_js']         = 1;
		$options['delay_js_scripts'] = $this->defaults;

		return $options;
	}

	/**
	 * Gets the data to populate the view for the restore defaults button
	 *
	 * @since 3.7
	 *
	 * @return array
	 */
	public function get_button_data() {
		return [
			'type'       => 'button',
			'action'     => 'rocket_delay_js_restore_defaults',
			'attributes' => [
				'label'      => __( 'Restore Defaults', 'rocket' ),
				'attributes' => [
					'class' => 'wpr-button wpr-button--icon wpr-button--purple wpr-icon-refresh',
				],
			],
		];
	}

	/**
	 * Sets the delay_js option to zero when updating to 3.7
	 *
	 * @since 3.7
	 *
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function set_option_on_update( $old_version ) {
		if ( version_compare( $old_version, '3.7', '>' ) ) {
			return;
		}

		$options = get_option( 'wp_rocket_settings', [] );

		$options['delay_js']         = 0;
		$options['delay_js_scripts'] = $this->defaults;

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Update delay_js options when updating to ver 3.7.4
	 *
	 * @since 3.7.4
	 *
	 * @param string $old_version Old plugin version.
	 *
	 * @return void
	 */
	public function option_update_3_7_4( $old_version ) {
		if ( version_compare( $old_version, '3.7.4', '>' ) ) {
			return;
		}

		$options          = get_option( 'wp_rocket_settings', [] );
		$delay_js_scripts = array_flip( $options['delay_js_scripts'] );

		if ( isset( $delay_js_scripts['adsbygoogle'] ) ) {
			$delay_js_scripts['adsbygoogle.js'] = $delay_js_scripts['adsbygoogle'];

			unset( $delay_js_scripts['adsbygoogle'] );
		}

		$options['delay_js_scripts'] = array_values( array_flip( $delay_js_scripts ) );

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Update delay_js options when updating to ver 3.7.2.
	 *
	 * @since 3.7.2
	 *
	 * @param string $old_version Old plugin version.
	 *
	 * @return void
	 */
	public function option_update_3_7_2( $old_version ) {
		if ( version_compare( $old_version, '3.7.2', '>' ) ) {
			return;
		}

		$options = get_option( 'wp_rocket_settings', [] );

		$delay_js_scripts = array_flip( $options['delay_js_scripts'] );

		if (
			isset( $delay_js_scripts['fbq('] )
			&&
			! isset( $delay_js_scripts['pixel-caffeine/build/frontend.js'] )
		) {
			$delay_js_scripts['pixel-caffeine/build/frontend.js'] = '';
		}

		if ( isset( $delay_js_scripts['google.com/recaptcha/api.js'] ) ) {
			unset( $delay_js_scripts['google.com/recaptcha/api.js'] );
		}

		if ( isset( $delay_js_scripts['widget.trustpilot.com'] ) ) {
			$delay_js_scripts['widget.trustpilot.com/bootstrap'] = $delay_js_scripts['widget.trustpilot.com'];

			unset( $delay_js_scripts['widget.trustpilot.com'] );
		}

		$options['delay_js_scripts'] = array_values( array_flip( $delay_js_scripts ) );

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Restores the default list when updating from 3.7.6 (which removed anything ending in '.js' -- whoops!)
	 *
	 * @since 3.7.6.1
	 *
	 * @param string $old_version Old plugin version.
	 *
	 * @return void
	 */
	public function option_update_3_7_6_1( $old_version ) {
		if ( 0 !== version_compare( $old_version, '3.7.6' ) ) {
			return;
		}

		$options = get_option( 'wp_rocket_settings', [] );

		if ( ! isset( $options['delay_js_scripts'] ) || ! is_array( $options['delay_js_scripts'] ) ) {
			$options['delay_js_scripts'] = $this->defaults;
		} else {
			$delay_js_scripts = array_flip( $options['delay_js_scripts'] );

			if ( isset( $delay_js_scripts['a.omappapi.com/app/js/api.min.js'] ) ) {
				unset( $delay_js_scripts['a.omappapi.com/app/js/api.min.js'] );
			}

			if ( isset( $delay_js_scripts['/sdk.js'] ) ) {
				unset( $delay_js_scripts['/sdk.js'] );
			}

			$options['delay_js_scripts'] = array_values( array_unique( array_merge( $this->defaults, array_flip( $delay_js_scripts ) ) ) );
		}

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Restores the delay_js_scripts option to the default value
	 *
	 * @since 3.7
	 *
	 * @return bool|string
	 */
	public function restore_defaults() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		return implode( "\n", $this->defaults );
	}
}
