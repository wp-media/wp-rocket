<?php
namespace WP_Rocket\Engine\Optimization\Minify\CSS;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Minify/Combine CSS Admin subscriber
 *
 * @since 3.5.3
 */
class AdminSubscriber implements Subscriber_Interface {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.5.3
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$slug = rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );

		return [
			'update_option_' . $slug     => [ 'clean_minify', 10, 2 ],
			'pre_update_option_' . $slug => [ 'regenerate_minify_css_key', 10, 2 ],
		];
	}

	/**
	 * Clean minify CSS files when options change.
	 *
	 * @since  3.5.3
	 *
	 * @param array $old_value An array of previous values for the settings.
	 * @param array $value     An array of submitted values for the settings.
	 */
	public function clean_minify( $old_value, $value ) {
		if ( ! is_array( $old_value ) || ! is_array( $value ) ) {
			return;
		}

		if ( ! $this->maybe_minify_regenerate( $value, $old_value ) ) {
			return;
		}
		// Purge all minify cache files.
		rocket_clean_minify( 'css' );
	}

	/**
	 * Regenerate the minify key if CSS files have been modified.
	 *
	 * @since  3.5.3
	 *
	 * @param array $value     An array of submitted values for the settings.
	 * @param array $old_value An array of previous values for the settings.
	 */
	public function regenerate_minify_css_key( $value, $old_value ) {
		if ( ! is_array( $old_value ) || ! is_array( $value ) ) {
			return $value;
		}

		if ( ! $this->maybe_minify_regenerate( $value, $old_value ) ) {
			return $value;
		}

		$value['minify_css_key'] = create_rocket_uniqid();
		return $value;
	}

	/**
	 * Checks minify CSS condition when options change.
	 *
	 * @since  3.5.3
	 *
	 * @param array $value     An array of submitted values for the settings.
	 * @param array $old_value An array of previous values for the settings.
	 */
	protected function maybe_minify_regenerate( $value, $old_value ) {
		if ( ( array_key_exists( 'minify_css', $old_value ) && array_key_exists( 'minify_css', $value ) && $old_value['minify_css'] !== $value['minify_css'] )
			||
			( array_key_exists( 'exclude_css', $old_value ) && array_key_exists( 'exclude_css', $value ) && $old_value['exclude_css'] !== $value['exclude_css'] )
			||
			( array_key_exists( 'cdn', $old_value ) && array_key_exists( 'cdn', $value ) && $old_value['cdn'] !== $value['cdn'] )
			||
			( array_key_exists( 'cdn_cnames', $old_value ) && array_key_exists( 'cdn_cnames', $value ) && $old_value['cdn_cnames'] !== $value['cdn_cnames'] )
		) {
			return true;
		}

		return false;
	}
}
