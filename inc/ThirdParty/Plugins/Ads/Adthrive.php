<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\Ads;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Adthrive implements Subscriber_Interface {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_rocket_upgrade'                      => [ 'add_delay_js_exclusion_on_plugin_update', 10, 2 ],
			'activate_adthrive-ads/adthrive-ads.php' => 'add_delay_js_exclusion',
		];
	}

	/**
	 * Adds adthrive to delay JS exclusion field
	 *
	 * @since 3.9.2
	 *
	 * @return void
	 */
	public function add_delay_js_exclusion() {
		$options = get_option( 'wp_rocket_settings', [] );

		if (
			! isset( $options['delay_js'] )
			||
			empty( $options['delay_js'] )
		) {
			return;
		}

		$exclusions = $options['delay_js_exclusions'] ?? [];

		if ( in_array( 'adthrive', $exclusions, true ) ) {
			return;
		}

		$exclusions[] = 'adthrive';

		$options['delay_js_exclusions'] = $exclusions;

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Adds adthrive to delay JS exclusion field on update to 3.9.2
	 *
	 * @since 3.9.2
	 *
	 * @param string $new_version Plugin new version.
	 * @param string $old_version Plugin old version.
	 *
	 * @return void
	 */
	public function add_delay_js_exclusion_on_plugin_update( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.9.2', '>' ) ) {
			return;
		}

		if ( is_plugin_active( 'adthrive-ads/adthrive-ads.php' ) ) {
			return;
		}

		$this->add_delay_js_exclusion();
	}
}
