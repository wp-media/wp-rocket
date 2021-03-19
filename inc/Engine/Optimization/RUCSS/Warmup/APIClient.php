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
		$args = [
			'body' => [
				'resources' => [
					$atts,
				],
			],
		];

		$request       = $this->handle_post( self::TREESHAKE_PATH, $args );
		$error_request = $this->handle_request_error( $request );

		return 200 === $error_request['code'];
	}

}
