<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins;

use Smush\Core\Settings;
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

		$prefix = rocket_get_constant( 'WP_SMUSH_PREFIX', 'wp-smush-' );

		return [
			'update_option_' . $prefix . 'settings'       => [ 'maybe_deactivate_rocket_lazyload', 11 ],
			'update_site_option_' . $prefix . 'settings'  => [ 'maybe_deactivate_rocket_lazyload', 11 ],
			'update_option_' . $prefix . 'lazy_load'      => [ 'maybe_deactivate_rocket_lazyload', 11 ],
			'update_site_option_' . $prefix . 'lazy_load' => [ 'maybe_deactivate_rocket_lazyload', 11 ],
			'rocket_maybe_disable_lazyload_helper'        => 'is_smush_lazyload_active',
		];
	}

	/**
	 * Disable WP Rocket lazyload when activating WP Smush and values are already in the database.
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 */
	public function maybe_deactivate_rocket_lazyload() {
		$enabled = $this->is_smush_lazyload_enabled();

		if ( $enabled['images'] && get_rocket_option( 'lazyload' ) ) {
			update_rocket_option( 'lazyload', 0 );
		}

		if ( $enabled['iframes'] && get_rocket_option( 'lazyload_iframes' ) ) {
			update_rocket_option( 'lazyload_iframes', 0 );
		}
	}

	/**
	 * Disable WP Rocket lazyload field for images if WP Smush lazyload is enabled.
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 *
	 * @param  array $disable_images_lazyload Array with plugins which disable lazyload functionality.
	 * @return array                          A list of plugin names.
	 */
	public function is_smush_lazyload_active( $disable_images_lazyload ) {
		$enabled = $this->is_smush_lazyload_enabled();

		if ( $enabled['images'] ) {
			$disable_images_lazyload[] = __( 'Smush', 'rocket' );
		}

		return $disable_images_lazyload;
	}

	/**
	 * Tell if Smush’s lazyload is enabled for each type of content.
	 *
	 * @since  3.5.1
	 * @author Grégory Viguier
	 *
	 * @return array {
	 *     @var bool $images  True when lazyload is enabled for images. False otherwise.
	 *     @var bool $iframes True when lazyload is enabled for iframes. False otherwise.
	 * }
	 */
	private function is_smush_lazyload_enabled() {
		$enabled = [
			'images'  => false,
			'iframes' => false,
		];

		if ( ! class_exists( '\\Smush\\Core\\Settings' ) ) {
			return $enabled;
		}

		$settings = Settings::get_instance();

		if ( ! $settings->get( 'lazy_load' ) ) {
			return $enabled;
		}

		$prefix  = rocket_get_constant( 'WP_SMUSH_PREFIX', 'wp-smush-' );
		$formats = $settings->get_setting( $prefix . 'lazy_load' );
		$formats = ! empty( $formats['format'] ) && is_array( $formats['format'] ) ? array_filter( $formats['format'] ) : [];

		$image_formats = array_intersect_key(
			$formats,
			// Whitelist image formats.
			[
				'jpeg' => false,
				'png'  => false,
				'gif'  => false,
				'svg'  => false,
			]
		);

		if ( $image_formats ) {
			// One or several image formats are enabled in Smush.
			$enabled['images'] = true;
		}

		if ( ! empty( $formats['iframe'] ) ) {
			// Iframe is enabled in Smush.
			$enabled['iframes'] = true;
		}

		return $enabled;
	}
}
