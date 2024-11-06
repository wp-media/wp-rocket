<?php

namespace WP_Rocket\Engine\Media\Fonts;

use WP_Rocket\Logger\Logger;

class GoogleFontFactory {
	/**
	 * Creates an instance of GoogleFontV1 or GoogleFontV2 based on the URL provided.
	 *
	 * @param string $url Google Fonts URL.
	 * @return GoogleFontVersion|null
	 */
	public static function create( string $url ): ?GoogleFontVersion {
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
}
