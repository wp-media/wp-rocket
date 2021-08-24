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
			'wp_rocket_upgrade'                      => [ 'add_delay_js_exclusion_on_plugin_update', 20, 2 ],
			'activate_adthrive-ads/adthrive-ads.php' => 'add_delay_js_exclusion',
			'pre_update_option_wp_rocket_settings'   => [ 'maybe_add_delay_js_exclusion', 10, 2 ],
		];
	}

	/**
	 * Adds adthrive to delay JS exclusion field
	 *
	 * @since 3.9.3
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
	 * Adds adthrive to delay JS exclusion field on update to 3.9.3
	 *
	 * @since 3.9.3
	 *
	 * @param string $new_version Plugin new version.
	 * @param string $old_version Plugin old version.
	 *
	 * @return void
	 */
	public function add_delay_js_exclusion_on_plugin_update( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.9.3', '>' ) ) {
			return;
		}

		if ( ! is_plugin_active( 'adthrive-ads/adthrive-ads.php' ) ) {
			return;
		}

		$this->add_delay_js_exclusion();
	}

	/**
	 * Adds Adthrive pattern when saving WPR options and Adthrive is enabled
	 *
	 * @since 3.9.3
	 *
	 * @param array $value     The new, unserialized option value.
	 * @param array $old_value The old option value.
	 *
	 * @return array
	 */
	public function maybe_add_delay_js_exclusion( $value, $old_value ): array {
		if ( ! is_plugin_active( 'adthrive-ads/adthrive-ads.php' ) ) {
			return $value;
		}

		if ( empty( $value['delay_js'] ) ) {
			return $value;
		}

		if (
			isset( $old_value['delay_js'] )
			&&
			$old_value['delay_js'] === $value['delay_js']
		) {
			return $value;
		}

		if (
			isset( $value['delay_js_exclusions'] )
			&&
			in_array( 'adthrive', $value['delay_js_exclusions'], true )
		) {
			return $value;
		}

		$value['delay_js_exclusions'][] = 'adthrive';

		return $value;
	}
}
