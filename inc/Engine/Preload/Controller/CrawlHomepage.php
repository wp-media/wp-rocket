<?php

namespace WP_Rocket\Engine\Preload\Controller;

class CrawlHomepage {

	/**
	 * Crawl the homepage.
	 *
	 * @return array|false
	 */
	public function crawl() {
		$user_agent = 'WP Rocket/Preload';

		/**
		 * Filters the arguments for the partial preload request.
		 *
		 * @param array $args Request arguments.
		 */
		$args = apply_filters(
			'rocket_homepage_preload_url_request_args',
			[
				'timeout'    => 10,
				'user-agent' => $user_agent,
				'sslverify'  => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			]
		);

		$response = wp_remote_get( esc_url_raw( home_url() ), $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			return false;
		}

		$content = wp_remote_retrieve_body( $response );

		preg_match_all( '/<a\s+(?:[^>]+?[\s"\']|)href\s*=\s*(["\'])(?<href>[^"\']+)\1/imU', $content, $urls );

		$home_url = home_url();

		$urls = array_map(
			static function ( $url ) use ( $home_url ) {
				if ( wp_parse_url( $url, PHP_URL_HOST ) || strpos( $url, '#' ) !== false ) {
					return $url;
				}
				return trailingslashit( $home_url ) . ltrim( wp_parse_url( $url, PHP_URL_PATH ), '/' );
			},
			$urls['href']
		);

		$urls = array_filter(
			$urls,
			static function ( $url ) use ( $home_url ) {
				return strpos( $url, $home_url ) !== false && strpos( $url, '#' ) === false;
			}
			);

		return array_values( array_unique( $urls ) );
	}
}
