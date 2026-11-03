<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

/**
 * Validates CDN CNAME URLs are reachable before rewrites are applied.
 *
 * Issues a HEAD request to the active theme stylesheet via the CNAME and caches
 * the result for one day. A hard HTTP 404 or a network/connection error disqualifies
 * a CNAME; all other HTTP status codes are treated as valid.
 */
class CNAMEValidator {

	/**
	 * Returns true when the CNAME responds with a non-404 HTTP code, false on a 404 or network error.
	 *
	 * @since 3.22.0.3
	 *
	 * @param string $cname_url Full CDN URL, e.g. "https://cdn.example.com".
	 * @return bool
	 */
	public function is_valid( string $cname_url ): bool {
		$transient_key = 'rocket_cname_valid_' . md5( $cname_url );
		$cached        = get_transient( $transient_key );

		if ( false !== $cached ) {
			return (bool) $cached;
		}

		$check_url = $this->build_check_url( $cname_url );
		$response  = wp_remote_head( $check_url, [ 'timeout' => 5 ] );

		if ( is_wp_error( $response ) ) {
			set_transient( $transient_key, 0, DAY_IN_SECONDS );
			return false;
		}

		$code  = isset( $response['response']['code'] ) ? (int) $response['response']['code'] : 0; // @phpstan-ignore-line - code may be absent in certain HTTP transport error states
		$valid = 404 !== $code ? 1 : 0;

		set_transient( $transient_key, $valid, DAY_IN_SECONDS );

		return (bool) $valid;
	}

	/**
	 * Deletes cached validation results for the given CNAME URLs.
	 *
	 * Called when CDN settings are saved so stale cached results are not used
	 * after the user updates their CNAME list.
	 *
	 * @since 3.22.0.3
	 *
	 * @param array $cname_urls List of CNAME URLs whose transients should be deleted.
	 * @return void
	 */
	public function clear_validation_cache( array $cname_urls ): void {
		foreach ( $cname_urls as $url ) {
			delete_transient( 'rocket_cname_valid_' . md5( rocket_add_url_protocol( $url ) ) );
		}
	}

	/**
	 * Builds the URL to probe by replacing the site host with the CDN host in the
	 * active theme's stylesheet URI.
	 *
	 * Using style.css guarantees the file exists on any WordPress install and
	 * that the check works with any CDN provider — not just RocketCDN.
	 *
	 * @since 3.22.0.3
	 *
	 * @param string $cname_url CDN URL.
	 * @return string
	 */
	private function build_check_url( string $cname_url ): string {
		$stylesheet_path = wp_parse_url( get_stylesheet_uri(), PHP_URL_PATH ) ?? '';
		$cname_host      = wp_parse_url( rocket_add_url_protocol( $cname_url ), PHP_URL_HOST ) ?? '';

		return 'https://' . $cname_host . $stylesheet_path;
	}
}
