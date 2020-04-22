<?php

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Admin\Options_Data;
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
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.6
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
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
		$fonts = array_map( [ $this, 'sanitize_font' ], $fonts );
		$fonts = array_filter( $fonts );

		if ( empty( $fonts ) ) {
			return;
		}

		$base_url = get_rocket_parse_url( site_url() );
		$base_url = "{$base_url['scheme']}://{$base_url['host']}";

		foreach ( array_unique( $fonts ) as $font ) {
			printf(
				"\n<link rel=\"preload\" as=\"font\" href=\"%s\" crossorigin>",
				esc_url( get_rocket_cdn_url( $base_url . $font ) )
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

		$ext     = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
		$formats = [
			'otf',
			'ttf',
			'svg',
			'woff',
			'woff2',
		];

		if ( ! in_array( $ext, $formats, true ) ) {
			return false;
		}

		return $file;
	}
}
