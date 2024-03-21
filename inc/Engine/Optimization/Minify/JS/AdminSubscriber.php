<?php

namespace WP_Rocket\Engine\Optimization\Minify\JS;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Minify/Combine JS Admin subscriber.
 */
class AdminSubscriber implements Subscriber_Interface {


	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$slug = rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );

		return [
			"update_option_{$slug}"     => [ 'clean_minify', 10, 2 ],
			"pre_update_option_{$slug}" => [ 'regenerate_minify_js_key', 10, 2 ],
		];
	}

	/**
	 * Clean minify JS files when options change.
	 *
	 * @param array $old_value The old option value.
	 * @param array $value The new option value.
	 */
	public function clean_minify( $old_value, $value ) {
		if ( ! is_array( $old_value ) || ! is_array( $value ) ) {
			return;
		}

		if ( ! $this->maybe_minify_regenerate( $value, $old_value ) ) {
			return;
		}
		// Purge all minify cache files.
		rocket_clean_minify( 'js' );
	}

	/**
	 * Regenerate the minify key if JS files have been modified.
	 *
	 * @since  3.5.4
	 *
	 * @param array $value The new option value.
	 * @param array $old_value The old option value.
	 *
	 * @return array Updates 'minify_js_key' setting when regenerated; else, original submitted settings.
	 */
	public function regenerate_minify_js_key( $value, $old_value ) {
		if ( ! is_array( $old_value ) || ! is_array( $value ) ) {
			return $value;
		}

		if ( ! $this->maybe_minify_regenerate( $value, $old_value ) ) {
			return $value;
		}

		$value['minify_js_key'] = create_rocket_uniqid();

		return $value;
	}

	/**
	 * Checks minify JS condition when options change.
	 *
	 * @since  3.5.4
	 *
	 * @param array $value The new option value.
	 * @param array $old_value The old option value.
	 *
	 * @return bool true when should regenerate; else false.
	 */
	protected function maybe_minify_regenerate( array $value, array $old_value ) {
		$settings_to_check = [
			'minify_js',
			'exclude_js',
			'cdn',
		];

		foreach ( $settings_to_check as $setting ) {
			if ( $this->did_setting_change( $setting, $value, $old_value ) ) {
				return true;
			}
		}

		return (
			array_key_exists( 'cdn', $value )
			&&
			1 === (int) $value['cdn']
			&&
			$this->did_setting_change( 'cdn_cnames', $value, $old_value )
		);
	}

	/**
	 * Checks if the given setting's value changed.
	 *
	 * @since 3.5.4
	 *
	 * @param string $setting The settings's value to check in the old and new values.
	 * @param array  $value     The new option value.
	 * @param array  $old_value     The old option value.
	 *
	 * @return bool
	 */
	protected function did_setting_change( $setting, array $value, array $old_value ) {
		return (
			array_key_exists( $setting, $old_value )
			&&
			array_key_exists( $setting, $value )
			&&
			$old_value[ $setting ] !== $value[ $setting ]
		);
	}
}
