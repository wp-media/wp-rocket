<?php

namespace WP_Rocket\Engine\Optimization;

class APIClient {
	const API_URL = 'https://example.org';

	public function optimize( $html, array $options ) {
		$args = [
			'body' => wp_json_encode(
				[
					'html'    => $html,
					'options' => $options,
				]
			),
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

		return wp_json_decode( wp_remote_retrieve_body( $request ) );
	}
}
