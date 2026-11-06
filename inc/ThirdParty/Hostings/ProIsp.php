<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with PRO ISP hosting.
 *
 * @since 3.13.1
 */
class ProIsp implements Subscriber_Interface {

	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'do_rocket_varnish_http_purge'            => 'is_varnish_active',
			'rocket_varnish_field_settings'           => 'maybe_set_varnish_addon_title',
			'rocket_display_input_varnish_auto_purge' => 'should_display_varnish_auto_purge_input',
		];
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
				'PRO ISP'
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
}
