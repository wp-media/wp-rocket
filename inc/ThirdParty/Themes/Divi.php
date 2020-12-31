<?php

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Divi implements Subscriber_Interface {
	/**
	 * Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class
	 *
	 * @param Options      $options_api Options API instance.
	 * @param Options_Data $options     WP Rocket options instance.
	 */
	public function __construct( Options $options_api, Options_Data $options ) {
		$this->options_api = $options_api;
		$this->options     = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [
			'switch_theme'                    => [ 'maybe_disable_youtube_preview', PHP_INT_MAX, 2 ],
			'rocket_specify_dimension_images' => 'disable_image_dimensions_height_percentage',
		];

		if ( ! self::is_divi() ) {
			return $events;
		}

		$events['rocket_exclude_js']                            = 'exclude_js';
		$events['rocket_maybe_disable_youtube_lazyload_helper'] = 'add_divi_to_description';

		return $events;
	}

	/**
	 * Excludes Divi's Salvatorre script from JS minification
	 *
	 * Prevent an error after minification/concatenation
	 *
	 * @since 3.6.3
	 *
	 * @param array $excluded_js An array of JS paths to be excluded.
	 *
	 * @return array the updated array of paths
	 */
	public function exclude_js( $excluded_js ) {
		if ( ! rocket_get_constant( 'ET_BUILDER_URI' ) ) {
			return $excluded_js;
		}

		$excluded_js[] = str_replace( home_url(), '', rocket_get_constant( 'ET_BUILDER_URI' ) . '/scripts/salvattore.min.js' );

		return $excluded_js;
	}

	/**
	 * Disables the Replace Youtube iframe by preview thumbnail option if new theme (or parent) is Divi
	 *
	 * @since 3.6.3
	 *
	 * @param string   $name  Name of the new theme.
	 * @param WP_Theme $theme instance of the new theme.
	 *
	 * @return void
	 */
	public function maybe_disable_youtube_preview( $name, $theme ) {
		if ( ! self::is_divi( $theme ) ) {

			return;
		}

		$this->options->set( 'lazyload_youtube', 0 );
		$this->options_api->set( 'settings', $this->options->get_options() );
	}

	/**
	 * Adds Divi to the array of items disabling Youtube lazyload
	 *
	 * @since 3.6.3
	 *
	 * @param array $disable_youtube_lazyload Array of items names.
	 *
	 * @return array
	 */
	public function add_divi_to_description( $disable_youtube_lazyload ) {
		if ( ! self::is_divi() ) {
			return $disable_youtube_lazyload;
		}

		$disable_youtube_lazyload[] = 'Divi';

		return $disable_youtube_lazyload;
	}

	/**
	 * Disables setting explicit dimensions on images where Divi calculates height as percentage.
	 *
	 * @since 3.8.2
	 *
	 * @param array $images The array of images selected for adding image dimensions.
	 *
	 * @return array The array without images using data-height-percentage.
	 */
	public function disable_image_dimensions_height_percentage( array $images ) {
		foreach ( $images as $key => $image ) {
			if ( false !== strpos( strtolower( $image ), 'data-height-percentage' ) ) {
				unset( $images[ $key ] );
			}
		}

		return $images;
	}

	/**
	 * Checks if the current theme (or parent) is Divi
	 *
	 * @since 3.6.3
	 *
	 * @param WP_Theme $theme Instance of the theme.
	 */
	private static function is_divi( $theme = null ) {
		$theme = $theme instanceof WP_Theme ? $theme : wp_get_theme();

		return ( 'Divi' === $theme->get( 'Name' ) || 'divi' === $theme->get_template() );
	}
}
