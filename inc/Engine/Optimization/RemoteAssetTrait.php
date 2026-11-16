<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization;

trait RemoteAssetTrait {

	/**
	 * Gets the body of a remote asset, or an empty string when the response is not a usable asset.
	 *
	 * @param string $url         Asset URL.
	 * @param bool   $reject_html Also reject responses served as text/html.
	 *
	 * @return string
	 */
	protected function get_remote_asset_content( string $url, bool $reject_html = true ): string {
		$response = wp_remote_get( $url );

		// Also covers WP_Error, for which the response code is an empty string.
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return '';
		}

		if ( $reject_html ) {
			$content_type = wp_remote_retrieve_header( $response, 'content-type' );

			if ( is_array( $content_type ) ) {
				$content_type = implode( ',', $content_type );
			}

			if ( false !== stripos( (string) $content_type, 'text/html' ) ) {
				return '';
			}
		}

		return (string) wp_remote_retrieve_body( $response );
	}
}
