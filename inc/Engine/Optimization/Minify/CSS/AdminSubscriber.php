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
			'update_option_' . $slug => [ 'clean_minify', 10, 2 ],
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
		// Purge all minify cache files.
		if ( ( isset( $old_value['minify_css'], $value['minify_css'] ) && $old_value['minify_css'] !== $value['minify_css'] )
			||
			( isset( $old_value['exclude_css'], $value['exclude_css'] ) && $old_value['exclude_css'] !== $value['exclude_css'] )
			||
			( isset( $old_value['cdn'], $value['cdn'] ) && $old_value['cdn'] !== $value['cdn'] )
			||
			( isset( $old_value['cdn_cnames'], $value['cdn_cnames'] ) && $old_value['cdn_cnames'] !== $value['cdn_cnames'] )
		) {
			rocket_clean_minify( 'css' );
		}
	}
}
