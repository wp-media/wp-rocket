<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status;

use WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient;

class APIClient extends AbstractAPIClient {

	/**
	 * API status path.
	 *
	 * @var string
	 */
	protected $request_path = 'resources/statusbyurl';

	/**
	 * Send the request to status endpoint.
	 *
	 * @param array|string[] $urls Resources' URLs.
	 *
	 * @return bool
	 */
	public function send_check_status_request( array $urls ): bool {
		$args = [
			'body' => [
				'urls' => [
					$urls,
				],
			],
		];

		return $this->handle_post( $args );
	}
}
