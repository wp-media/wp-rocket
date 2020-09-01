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
		'google.com/recaptcha/api.js',
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
		'adsbygoogle',
		'ShopifyBuy',
		'widget.trustpilot.com',
		'ft.sdk.min.js',
		'apps.elfsight.com/p/platform.js',
		'livechatinc.com/tracking.js',
		'LiveChatWidget',
		'/busting/facebook-tracking/',
		'olark',
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
