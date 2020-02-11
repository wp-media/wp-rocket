<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Smush
 *
 * @since  3.4.2
 * @author Soponar Cristina
 */
class Smush_Subscriber implements Subscriber_Interface {
	/**
	 * Subscribed events for Smush.
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		if ( ! rocket_has_constant( 'WP_SMUSH_VERSION' ) ) {
			return [
				'activate_wp-smushit/wp-smush.php' => [ 'maybe_deactivate_rocket_lazyload', 10 ],
			];
		}

		return [
			'update_option_wp-smush-settings'      => [ 'maybe_deactivate_rocket_lazyload', 11 ],
			'rocket_maybe_disable_lazyload_helper' => 'is_smush_lazyload_active',
		];
	}

	/**
	 * Disable WP Rocket lazyload when activating WP Smush and values are already in the database.
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 */
	public function maybe_deactivate_rocket_lazyload() {
		$lazy_load_option = get_option( rocket_get_constant( 'WP_SMUSH_PREFIX' ) . 'settings' );
		$lazyload         = isset( $lazy_load_option['lazy_load'] ) ? $lazy_load_option['lazy_load'] : false;

		if ( ! $lazyload ) {
			return;
		}

		update_rocket_option( 'lazyload', 0 );
	}

	/**
	 * Disable WP Rocket lazyload fields if WP Smush lazyload is enabled
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 *
	 * @param array $rocket_maybe_disable_lazyload_plugins Array with plugins which disable lazyload functionality.
	 * @return string
	 */
	public function is_smush_lazyload_active( $rocket_maybe_disable_lazyload_plugins ) {
		$lazy_load_option = get_option( rocket_get_constant( 'WP_SMUSH_PREFIX' ) . 'settings' );
		$lazyload         = isset( $lazy_load_option['lazy_load'] ) ? $lazy_load_option['lazy_load'] : false;

		if ( ! $lazyload ) {
			return $rocket_maybe_disable_lazyload_plugins;
		}

		$rocket_maybe_disable_lazyload_plugins[] = __( 'Smush', 'rocket' );
		return $rocket_maybe_disable_lazyload_plugins;
	}
}
