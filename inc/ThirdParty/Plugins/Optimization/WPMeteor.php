<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;

class WPMeteor implements Subscriber_Interface {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_delay_js_settings_field'       => 'maybe_disable_delay_js_field',
			'activate_wp-meteor/wp-meteor.php'     => 'disable_delay_js',
			'wp_rocket_upgrade'                    => [ 'maybe_disable_delay_js', 10, 2 ],
			'pre_update_option_wp_rocket_settings' => 'disable_delay_js_on_option_update',
		];
	}

	/**
	 * Disable the delay JS field when WP Meteor is active
	 *
	 * @since 3.9.2
	 *
	 * @param array $field Delay JS field data array.
	 *
	 * @return array
	 */
	public function maybe_disable_delay_js_field( $field ): array {
		if ( ! is_plugin_active( 'wp-meteor/wp-meteor.php' ) ) {
			return $field;
		}

		$field['container_class'][]      = 'wpr-isDisabled';
		$field['value']                  = 0;
		$field['input_attr']['disabled'] = 1;
		$field['helper']                 = sprintf(
			// translators: %1$s = plugin name.
			__( 'Delay JS is currently activated in %1$s. If you want to use WP Rocket’s delay JS, disable %1$s', 'rocket' ),
			'WP Meteor'
		);

		return $field;
	}

	/**
	 * Disable delay JS option when WP Meteor is activated
	 *
	 * @since 3.9.2
	 *
	 * @return void
	 */
	public function disable_delay_js() {
		$options = get_option( 'wp_rocket_settings', [] );

		$options['delay_js'] = 0;

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Disable delay JS when updating to 3.9.2 and above and WP Meteor is active
	 *
	 * @since 3.9.2
	 *
	 * @param string $new_version Plugin new version.
	 * @param string $old_version Plugin old version.
	 *
	 * @return void
	 */
	public function maybe_disable_delay_js( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.9.2', '>' ) ) {
			return;
		}

		if ( ! is_plugin_active( 'wp-meteor/wp-meteor.php' ) ) {
			return;
		}

		$this->disable_delay_js();
	}

	/**
	 * Disable delay JS on WP Rocket settings update if WP Meteor is active
	 *
	 * @since 3.9.2
	 *
	 * @param mixed $value The new, unserialized option value.
	 *
	 * @return mixed
	 */
	public function disable_delay_js_on_option_update( $value ) {
		if ( ! is_plugin_active( 'wp-meteor/wp-meteor.php' ) ) {
			return $value;
		}

		if ( ! isset( $value['delay_js'] ) ) {
			return $value;
		}

		if ( 0 === (int) $value['delay_js'] ) {
			return $value;
		}

		$value['delay_js'] = 0;

		return $value;
	}
}
