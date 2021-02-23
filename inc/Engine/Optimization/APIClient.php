<?php

namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\Logger\Logger;

class APIClient {
	/**
	 * RUCSS API Url.
	 */
	const API_URL = 'https://saas.wp-rocket.me/api/';

	/**
	 * Optimize page HTML.
	 *
	 * @param string $html Page HTML.
	 * @param array  $options Array of options.
	 *
	 * @return mixed
	 */
	public function optimize( $html, array $options ) {
		global $wp;

		timer_start();

		$url = home_url( add_query_arg( [], $wp->request ) );

		$args = [
			'body'    => [
				'html'   => $html,
				'url'    => $url,
				'config' => $options,
			],
			'timeout' => 30,
		];

		$request = wp_remote_post(
			self::API_URL,
			$args
		);

		if ( is_wp_error( $request ) ) {
			Logger::error( 'RUCSS request failure', [ 'error' => $request->get_error_message() ] );
			return $html;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
			Logger::error( 'RUCSS response code is not valid', [ 'code' => wp_remote_retrieve_response_code( $request ) ] );

			return $html;
		}

		Logger::debug( 'RUCSS Optimization time', [ 'elapsed' => timer_stop() ] );

		return json_decode( wp_remote_retrieve_body( $request ) );
	}
}
