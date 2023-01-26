<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Subscriber for compatibility with One.com hosting.
 *
 * @since 3.12.1
 */
class OneCom implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @since 3.6.3
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'pre_get_rocket_option_cdn'               => 'maybe_enable_cdn_option',
			'pre_get_rocket_option_cdn_cnames'        => 'maybe_update_cdn_cname',
			'pre_get_rocket_option_cdn_zone'          => 'maybe_update_cdn_zone',
			'rocket_cdn_reject_files'                 => 'exclude_from_cdn',
			'rocket_disable_cdn_option_change'        => 'is_oc_cdn_enabled',
			'rocket_cdn_settings_fields'              => 'disable_cdn_change',
			'do_rocket_varnish_http_purge'            => 'is_varnish_active',
			'rocket_varnish_field_settings'           => 'maybe_set_varnish_addon_title',
			'rocket_display_input_varnish_auto_purge' => 'should_display_varnish_auto_purge_input',
			'rocket_display_rocketcdn_cta'            => 'return_false',
			'rocket_display_rocketcdn_status'         => 'return_false',
			'rocket_promote_rocketcdn_notice'         => 'return_false',
		];
	}

	/**
	 * Check if one.com cdn is enabled.
	 *
	 * @return boolean
	 */
	public function is_oc_cdn_enabled(): bool {
		return rocket_get_constant( 'vcaching', false ) && rest_sanitize_boolean( get_option( 'oc_cdn_enabled' ) );
	}

	/**
	 * Enable CDN option.
	 *
	 * @param string|null $cdn CDN Option.
	 * @return bool|null
	 */
	public function maybe_enable_cdn_option( ?string $cdn ) {
		return $this->is_oc_cdn_enabled() ? true : $cdn;
	}

	/**
	 * Update CNAME
	 *
	 * @param string|null $cname CDN CNAME.
	 * @return array|null
	 */
	public function maybe_update_cdn_cname( ?string $cname ) {
		return $this->is_oc_cdn_enabled() ? [ $this->build_cname() ] : $cname;
	}

	/**
	 * Update CDN Zones.
	 *
	 * @param string|null $zone CDN ZONES.
	 * @return array|null
	 */
	public function maybe_update_cdn_zone( ?string $zone ) {
		return $this->is_oc_cdn_enabled() ? [ 'all' ] : $zone;
	}

	/**
	 * Exclude files from being rewritten.
	 * From 3.12.5.2 we are excluding new wp-content directory paths if it's not the normal one.
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
		return ! $this->is_varnish_active();
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
		return rocket_get_constant( 'vcaching', false ) && rest_sanitize_boolean( get_option( 'varnish_caching_enable' ) );
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
}
