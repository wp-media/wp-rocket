<?php

namespace WP_Rocket\Engine\Media\Fonts\Factory;

use WP_Rocket\Engine\Media\Fonts\Factory\Fonts\GoogleFontV1;
use WP_Rocket\Engine\Media\Fonts\Factory\Fonts\GoogleFontV2;
use WP_Rocket\Logger\Logger;

class GoogleFontFactory {
	/**
	 * The Google Fonts URL.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * The Google Font version instance.
	 *
	 * @var GoogleFontVersion|null
	 */
	protected $font_version;

	/**
	 * Constructor.
	 *
	 * @param string $url Google Fonts URL.
	 */
	public function __construct( string $url ) {
		$this->url          = $url;
		$this->font_version = $this->create_font_version( $url );
	}

	/**
	 * Creates an instance of GoogleFontV1 or GoogleFontV2 based on the URL provided.
	 *
	 * @param string $url Google Fonts URL.
	 * @return GoogleFontVersion|null
	 */
	protected function create_font_version( string $url ): ?GoogleFontVersion {
		if ( strpos( $url, 'css2' ) !== false ) {
			return new GoogleFontV2( $url );
		}

		if ( strpos( $url, 'fonts.googleapis.com' ) !== false ) {
			return new GoogleFontV1( $url );
		}

		// Log an error message for invalid or malformed URLs.
		Logger::error( 'Invalid or malformed Google Fonts URL: ' . $url );

		return null;
	}

	/**
	 * Get the Google Font version instance.
	 *
	 * @return GoogleFontVersion|null
	 */
	public function get_font_version(): ?GoogleFontVersion {
		return $this->font_version;
	}
}
