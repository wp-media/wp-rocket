<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * Subscriber for compatibility with One.com hosting.
 *
 * @since 3.12.1
 */
class OneCom implements Subscriber_Interface {

	/**
	 * WP Options API instance
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * CDN CNAMES.
	 *
	 * @var array
	 */
	private $cdn_cnames = [];

	/**
	 * CDN ZONES.
	 *
	 * @var array
	 */
	private $cdn_zones = [];

	/**
	 * CDN option.
	 *
	 * @var boolean
	 */
	private $cdn = false;

	/**
	 * Constructor
	 *
	 * @param Options      $options_api WP Options API instance.
	 * @param Options_Data $options     WP Rocket Options instance.
	 */
	public function __construct( Options $options_api, Options_Data $options ) {
		$this->options_api = $options_api;
		$this->options     = $options;
	}

	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @since 3.6.3
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'init'                                    => [
				[ 'maybe_enable_cdn_option' ],
				[ 'maybe_disable_cdn_option' ],
			],
			'rocket_cdn_reject_files'                 => 'exclude_from_cdn',
			'rocket_disable_cdn_option_change'        => 'is_oc_cdn_enabled',
			'rocket_cdn_settings_fields'              => 'disable_cdn_change',
			'do_rocket_varnish_http_purge'            => 'is_varnish_active',
			'rocket_varnish_field_settings'           => 'maybe_set_varnish_addon_title',
			'rocket_display_input_varnish_auto_purge' => 'should_display_varnish_auto_purge_input',
		];
	}

	/**
	 * Check if one.com cdn is enabled.
	 *
	 * @return boolean
	 */
	public function is_oc_cdn_enabled(): bool {
		return rest_sanitize_boolean( get_option( 'oc_cdn_enabled' ) );
	}

	/**
	 * Enable CDN option.
	 *
	 * @return void
	 */
	public function maybe_enable_cdn_option() {

		if ( ! $this->is_oc_cdn_enabled() ) {
			return;
		}

		$this->get_cdn_options();

		$cdn_url = $this->build_cname();

		if ( $this->cdn ) {
			return;
		}

		$this->cdn_cnames = [ $cdn_url ];
		$this->cdn_zones  = [ 'all' ];

		$this->update_cdn_options( 1, $this->cdn_cnames, $this->cdn_zones );

		rocket_clean_domain();
	}

	/**
	 * Disable cdn option.
	 *
	 * @return void
	 */
	public function maybe_disable_cdn_option() {

		if ( $this->is_oc_cdn_enabled() ) {
			return;
		}

		$this->get_cdn_options();

		if ( ! $this->cdn ) {
			return;
		}

		$cdn_url = $this->build_cname();

		// Remove CDN CNAME.
		$cname_index_key = array_search( $cdn_url, $this->cdn_cnames, true );
		unset( $this->cdn_cnames[ $cname_index_key ] );

		// Remove CDN Zone.
		unset( $this->cdn_zones[ $cname_index_key ] );

		$this->update_cdn_options( 0, $this->cdn_cnames, $this->cdn_zones );

		rocket_clean_domain();
	}

	/**
	 * Exclude files from being rewritten.
	 *
	 * @param array $files Array of files to be excluded.
	 * @return array
	 */
	public function exclude_from_cdn( array $files ): array {

		if ( ! $this->is_oc_cdn_enabled() ) {
			return $files;
		}

		$files[] = '/wp-includes/(.*)';

		return $files;
	}

	/**
	 * Disable CDN option change.
	 *
	 * @param array $settings CDN field settings data.
	 * @return array
	 */
	public function disable_cdn_change( array $settings ): array {
		if ( ! $this->is_oc_cdn_enabled() ) {
			return $settings;
		}

		$settings['cdn']['container_class'][]      = 'wpr-isDisabled';
		$settings['cdn']['input_attr']['disabled'] = 1;

		return $settings;
	}

	/**
	 * Purge varnish cache.
	 *
	 * @return boolean
	 */
	public function should_display_varnish_auto_purge_input(): bool {
		return $this->is_varnish_active() ? false : true;
	}

	/**
	 * Set varnish addon title
	 *
	 * @param array $settings Varnish settings field data.
	 * @return array
	 */
	public function maybe_set_varnish_addon_title( array $settings ): array {

		// Bail out if varnish is disabled.
		if ( ! $this->is_varnish_active() ) {
			return $settings;
		}

		$settings['varnish_auto_purge']['title'] = sprintf(
			// Translators: %s = Hosting name.
				__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
				'One.com'
			);

		return $settings;
	}

	/**
	 * Check if varnish option is enabled.
	 *
	 * @return boolean
	 */
	public function is_varnish_active() {
		return rest_sanitize_boolean( get_option( 'varnish_caching_enable' ) );
	}

	/**
	 * Build CDN CNAME.
	 *
	 * @return string
	 */
	public function build_cname(): string {
		if ( ! isset( $_SERVER['ONECOM_DOMAIN_NAME'] ) && ! isset( $_SERVER['HTTP_HOST'] ) ) {
			return '';
		}

		$domain_name = sanitize_text_field( wp_unslash( $_SERVER['ONECOM_DOMAIN_NAME'] ) );
		$http_host   = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );

		$is_subdomain = '' === str_replace( $domain_name, '', $http_host ) ? false : true;

		return $is_subdomain ? "usercontent.one/wp/$http_host" : "usercontent.one/wp/www.$http_host";
	}

	/**
	 * Get CDN options.
	 *
	 * @return void
	 */
	private function get_cdn_options() {
		$this->cdn_cnames = $this->options->get( 'cdn_cnames', [] );
		$this->cdn_zones  = $this->options->get( 'cdn_zone', [] );
		$this->cdn        = (bool) $this->options->get( 'cdn', 0 );
	}

	/**
	 * Update CDN options.
	 *
	 * @param integer $enable_cdn Enable/Disable CDN option.
	 * @param array   $cdn_cnames CDN CNAMES.
	 * @param array   $cdn_zones CDN Zones.
	 * @return void
	 */
	private function update_cdn_options( int $enable_cdn, array $cdn_cnames, array $cdn_zones ) {
		$this->options->set( 'cdn', $enable_cdn );
		$this->options->set( 'cdn_cnames', $cdn_cnames );
		$this->options->set( 'cdn_zone', $cdn_zones );

		$this->options_api->set( 'settings', $this->options->get_options() );
	}
}
