<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Frontend;

use WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient;

class APIClient extends AbstractAPIClient {

	/**
	 * SaaS main API path.
	 *
	 * @var string
	 */
	protected $request_path = 'rucss-job';

	/**
	 * Calls Central SaaS API.
	 *
	 * @param string $html    HTML content.
	 * @param string $url     HTML url.
	 * @param array  $options Array with options sent to Saas API.
	 *
	 * @return array
	 */
	public function add_to_queue( string $url, array $options ): array {
		$args = [
			'body'    => [
				'url'    => $url,
				'config' => $options,
			],
			'timeout' => 5,
		];

		$sent = $this->handle_post( $args );

		if ( ! $sent ) {
			return [
				'code'    => $this->response_code,
				'message' => $this->error_message,
			];
		}

		$default = [
			'code'     => 400,
			'message'  => 'Bad json',
			'contents' => [
				'jobId'      => 0,
				'queueName' => '',
			],
		];

		$result = json_decode( $this->response_body, true );
		return wp_parse_args( (array) $result, $default );
	}

	public function get_queue_job_status( $job_id, $queue_name ) {

	}
}
