<?php

namespace WP_Rocket\Engine\Media\Fonts\Factory\Fonts;

use WP_Rocket\Engine\Media\Fonts\Factory\GoogleFontVersion;

class GoogleFontV1 extends GoogleFontVersion {
	/**
	 * Base URL for Google Font V1.
	 *
	 * @var string
	 */
	protected $url = 'https://fonts.googleapis.com/css';

	/**
	 * Constructor.
	 *
	 * @param string $url Google Fonts URL.
	 */
	public function __construct( string $url ) {
		$this->url = $url;
	}

	/**
	 * Get the local URL for the Google Font V1.
	 *
	 * @return string
	 */
	public function get_local_url(): string {
		// To be replaced when developing the backend.
		$hash = md5( $this->url );
		return home_url( "/wp-content/cache/wp-rocket/fonts/google-fonts/1/{$hash}.css" );
	}
}
