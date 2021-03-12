<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

class APIClient {

	/**
	 * Warmup API url.
	 */
	const API_URL = 'https://central-saas.wp-rocket.me:30443/warmup';

	/**
	 * Send the request to Warmup.
	 *
	 * @param array $atts Resource attributes.
	 *
	 * @return bool
	 */
	public function send_warmup_request( $atts ) {
		$atts = wp_parse_args(
			$atts,
			[
				'url'     => '',
				'type'    => 'css',
				'content' => '',
			]
		);

		$response = wp_remote_post(
			self::API_URL,
			[
				'body' => [
					'resources' => [
						$atts,
					],
				],
			]
		);

		return 200 === wp_remote_retrieve_response_code( $response );
	}

}
