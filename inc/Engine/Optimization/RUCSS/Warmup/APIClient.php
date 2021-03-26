<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient;

class APIClient extends AbstractAPIClient {

	/**
	 * API Warmup path.
	 *
	 * @var string
	 */
	protected $request_path = 'warmup';

	/**
	 * Send the request to Warmup.
	 *
	 * @param array $atts Resource attributes.
	 *
	 * @return bool
	 */
	public function send_warmup_request( $atts ): bool {
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

		return $this->handle_post( $args );
	}
}
