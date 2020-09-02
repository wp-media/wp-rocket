<?php

namespace WP_Rocket\Engine\Optimization;

class APIClient {
	const API_URL = 'https://tree.wp-rocket.me/';

	public function optimize( $html, array $options ) {
		global $wp;

		timer_start();

		$url = home_url( add_query_arg( array(), $wp->request ) );

		$args = [
			'body' => [
				'html'    => $html,
				'url'     => $url,
				'config' => $options,
			]
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

		error_log( 'Optimization time: ' . timer_stop() );

		return wp_remote_retrieve_body( $request );
	}
}
