<?php

namespace WP_Rocket\ThirdParty\Plugins;

use Smush\Core\Settings;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Smush
 *
 * @since  3.4.2
 * @author Soponar Cristina
 */
class Smush implements Subscriber_Interface {
	/**
	 * WP Options API instance
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

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
			'update_option_wp-smush-settings'              => [ 'maybe_deactivate_rocket_lazyload', 11 ],
			'update_site_option_wp-smush-settings'         => [ 'maybe_deactivate_rocket_lazyload', 11 ],
			'update_option_wp-smush-lazy_load'             => [ 'maybe_deactivate_rocket_lazyload', 11 ],
			'update_site_option_wp-smush-lazy_load'        => [ 'maybe_deactivate_rocket_lazyload', 11 ],
			'rocket_maybe_disable_lazyload_helper'         => 'is_smush_lazyload_active',
			'rocket_maybe_disable_iframes_lazyload_helper' => 'is_smush_iframes_lazyload_active',
		];
	}

	/**
	 * Constructor.
	 *
	 * @since 3.5.5
	 *
	 * @param Options      $options_api WP Options API instance.
	 * @param Options_Data $options     WP Rocket Options instance.
	 */
	public function __construct( Options $options_api, Options_Data $options ) {
		$this->options_api = $options_api;
		$this->options     = $options;
	}

	/**
	 * Disable WP Rocket lazyload when activating WP Smush and values are already in the database.
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 */
	public function maybe_deactivate_rocket_lazyload() {
		$enabled = $this->is_smush_lazyload_enabled();
		$updated = false;

		if ( $enabled['images'] && $this->options->get( 'lazyload' ) ) {
			$this->options->set( 'lazyload', 0 );
			$updated = true;
		}

		if ( $enabled['iframes'] && $this->options->get( 'lazyload_iframes' ) ) {
			$this->options->set( 'lazyload_iframes', 0 );
			$updated = true;
		}

		if ( ! $updated ) {
			return;
		}

		$this->options_api->set( 'settings', $this->options->get_options() );
	}

	/**
	 * Add "Smush" to the provided array if WP Smush lazyload is enabled for images.
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 *
	 * @param  array $disable_images_lazyload Array with plugins which disable lazyload functionality.
	 * @return array                          A list of plugin names.
	 */
	public function is_smush_lazyload_active( array $disable_images_lazyload ) {
		$enabled = $this->is_smush_lazyload_enabled();

		if ( $enabled['images'] ) {
			$disable_images_lazyload[] = __( 'Smush', 'rocket' );
		}

		return $disable_images_lazyload;
	}

	/**
	 * Add "Smush" to the provided array if WP Smush lazyload is enabled for iframes.
	 *
	 * @since 3.5.5
	 *
	 * @param  array $disable_iframes_lazyload Array with plugins which disable lazyload functionality.
	 * @return array                           A list of plugin names.
	 */
	public function is_smush_iframes_lazyload_active( $disable_iframes_lazyload ) {
		$enabled = $this->is_smush_lazyload_enabled();

		if ( $enabled['iframes'] ) {
			$disable_iframes_lazyload[] = __( 'Smush', 'rocket' );
		}

		return $disable_iframes_lazyload;
	}

	/**
	 * Tell if Smush’s lazyload is enabled for each type of content.
	 *
	 * @since 3.5.5
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

		if ( ! class_exists( '\Smush\Core\Settings' ) ) {
			return $enabled;
		}

		if ( ! method_exists( '\Smush\Core\Settings', 'get_instance' ) ) {
			return $enabled;
		}

		$smush_settings = Settings::get_instance();

		if ( ! method_exists( $smush_settings, 'get' ) || ! method_exists( $smush_settings, 'get_setting' ) ) {
			return $enabled;
		}

		if ( ! $smush_settings->get( 'lazy_load' ) ) {
			return $enabled;
		}

		$formats = $smush_settings->get_setting( 'wp-smush-lazy_load' );
		$formats = ! empty( $formats['format'] ) && is_array( $formats['format'] ) ? array_filter( $formats['format'] ) : [];

		$image_formats = array_intersect_key(
			$formats,
			// Allowlist image formats.
			[
				'jpeg' => false,
				'png'  => false,
				'gif'  => false,
				'svg'  => false,
			]
		);

		if ( ! empty( $image_formats ) ) {
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
