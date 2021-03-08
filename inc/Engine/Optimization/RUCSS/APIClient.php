<?php

namespace WP_Rocket\Engine\Optimization\RUCSS;

class APIClient {
	const API_URL = 'https://saas.wp-rocket.me/api/';

	public function optimize( $html, array $options ) {
		global $wp;

		$url = home_url( add_query_arg( [], $wp->request ) );

		$args = [
			'body' => [
				'html'    => $html,
				'url'     => $url,
				'config' => $options,
			],
			'timeout' => 30,
		];


		$request = wp_remote_post(
			self::API_URL,
			$args
		);

		if ( is_wp_error( $request ) ) {
			return $html;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {

			return $html;
		}

		return json_decode( wp_remote_retrieve_body( $request ) );
	}
}
