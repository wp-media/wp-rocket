<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

class APIClient {

	const API_URL = 'https://central-saas.wp-rocket.me:30443/warmup';

	public function send_warmup_request( $atts ) {
		$atts = wp_parse_args(
			$atts,
			[
				'url' => '',
				'type' => 'css',
				'content' => ''
			]
		);

		$response = wp_remote_post(
			self::API_URL,
			[
				'body' => $atts,
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		return true;
	}

}
