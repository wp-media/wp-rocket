<?php

namespace WP_Rocket\Engine\Preload\Controller;

class CrawlHomepage
{
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

		return array_unique( $urls['href'] );
	}
}
