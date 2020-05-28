<?php

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Fonts preload.
 *
 * @since 3.6
 */
class Fonts implements Subscriber_Interface {

	/**
	 * WP Rocket Options instance.
	 *
	 * @since 3.6
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * WP Rocket CDN instance.
	 *
	 * @since 3.6
	 *
	 * @var CDN
	 */
	private $cdn;

	/**
	 * Font formats allowed to be preloaded.
	 *
	 * @since 3.6
	 * @see   $this->sanitize_font()
	 *
	 * @var string|bool
	 */
	private $font_formats = [
		'otf',
		'ttf',
		'svg',
		'woff',
		'woff2',
	];

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.6
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 * @param CDN          $cdn     WP Rocket CDN instance.
	 */
	public function __construct( Options_Data $options, CDN $cdn ) {
		$this->options = $options;
		$this->cdn     = $cdn;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.6
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'wp_head' => [ 'preload_fonts' ],
		];
	}

	/**
	 * Add the required <link/> tags used to preload fonts.
	 *
	 * @since 3.6
	 */
	public function preload_fonts() {
		$fonts = $this->options->get( 'preload_fonts', [] );
		/**
		 * Filters the list of fonts to preload
		 *
		 * @since 3.6
		 *
		 * @param array $fonts Array of fonts paths.
		 */
		$fonts = (array) apply_filters( 'rocket_preload_fonts', $fonts );
		$fonts = array_map( [ $this, 'sanitize_font' ], $fonts );
		$fonts = array_filter( $fonts );

		if ( empty( $fonts ) ) {
			return;
		}

		$base_url = get_rocket_parse_url( home_url() );
		$base_url = "{$base_url['scheme']}://{$base_url['host']}";

		foreach ( array_unique( $fonts ) as $font ) {
			printf(
				"\n<link rel=\"preload\" as=\"font\" href=\"%s\" crossorigin>",
				esc_url( $this->cdn->rewrite_url( $base_url . $font ) )
			);
		}
	}

	/**
	 * Sanitize a font file path.
	 *
	 * @since 3.6
	 *
	 * @param  string $file Filepath to sanitize.
	 * @return string|bool  Sanitized filepath. False if not a font file.
	 */
	private function sanitize_font( $file ) {
		if ( ! is_string( $file ) ) {
			return false;
		}

		$file = trim( $file );

		if ( empty( $file ) ) {
			return false;
		}

		$ext = strtolower( pathinfo( wp_parse_url( $file, PHP_URL_PATH ), PATHINFO_EXTENSION ) );

		if ( ! in_array( $ext, $this->font_formats, true ) ) {
			return false;
		}

		return $file;
	}
}
