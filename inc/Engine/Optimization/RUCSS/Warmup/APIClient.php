<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient;

class APIClient extends AbstractAPIClient {

	/**
	 * API Warmup path.
	 */
	const WARMUP_PATH = 'warmup';

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
			self::API_URL . self::WARMUP_PATH,
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
