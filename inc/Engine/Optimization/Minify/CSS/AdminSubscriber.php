<?php

namespace WP_Rocket\Engine\Optimization\Minify\CSS;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Minify/Combine CSS Admin subscriber
 *
 * @since 3.5.4
 */
class AdminSubscriber implements Subscriber_Interface {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.5.4
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$slug = rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );

		return [
			"update_option_{$slug}"     => [ 'clean_minify', 10, 2 ],
			"pre_update_option_{$slug}" => [ 'regenerate_minify_css_key', 10, 2 ],
		];
	}

	/**
	 * Clean minify CSS files when options change.
	 *
	 * @since  3.5.4
	 *
	 * @param array $old An array of previous settings.
	 * @param array $new An array of submitted settings.
	 */
	public function clean_minify( $old, $new ) {
		if ( ! is_array( $old ) || ! is_array( $new ) ) {
			return;
		}

		if ( ! $this->maybe_minify_regenerate( $new, $old ) ) {
			return;
		}
		// Purge all minify cache files.
		rocket_clean_minify( 'css' );
	}

	/**
	 * Regenerate the minify key if CSS files have been modified.
	 *
	 * @since  3.5.4
	 *
	 * @param array $new An array of submitted settings.
	 * @param array $old An array of previous settings.
	 *
	 * @return array Updates 'minify_css_key' setting when regenerated; else, original submitted settings.
	 */
	public function regenerate_minify_css_key( $new, $old ) {
		if ( ! is_array( $old ) || ! is_array( $new ) ) {
			return $new;
		}

		if ( ! $this->maybe_minify_regenerate( $new, $old ) ) {
			return $new;
		}

		$new['minify_css_key'] = create_rocket_uniqid();

		return $new;
	}

	/**
	 * Checks minify CSS condition when options change.
	 *
	 * @since  3.5.4
	 *
	 * @param array $new An array of submitted settings.
	 * @param array $old An array of previous settings.
	 *
	 * @return bool true when should regenerate; else false.
	 */
	protected function maybe_minify_regenerate( array $new, array $old ) {
		$settings_to_check = [
			'minify_css',
			'exclude_css',
			'cdn',
		];

		foreach ( $settings_to_check as $setting ) {
			if ( $this->did_setting_change( $setting, $new, $old ) ) {
				return true;
			}
		}

		return (
			array_key_exists( 'cdn', $new )
			&&
			1 === (int) $new['cdn']
			&&
			$this->did_setting_change( 'cdn_cnames', $new, $old )
		);
	}

	/**
	 * Checks if the given setting's value changed.
	 *
	 * @since 3.5.4
	 *
	 * @param string $setting The settings's value to check in the old and new values.
	 * @param array  $new     An array of submitted settings.
	 * @param array  $old     An array of previous settings.
	 *
	 * @return bool
	 */
	protected function did_setting_change( $setting, array $new, array $old ) {
		return (
			array_key_exists( $setting, $old )
			&&
			array_key_exists( $setting, $new )
			&&
			$old[ $setting ] !== $new[ $setting ]
		);
	}
}
