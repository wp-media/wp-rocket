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
	 * @param string $url Page url.
	 * @param array  $options Array with options sent to Saas API.
	 *
	 * @return array
	 */
	public function add_to_queue( string $url, array $options ): array {
		$args = [
			'body'    => [
				'url'    => add_query_arg(
					[
						'nowprocket'  => 1,
						'no_optimize' => 1,
					],
					$url
				),
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
			'message'  => 'No message. Defaulted in add_to_queue',
			'contents' => [
				'jobId'     => '0',
				'queueName' => '',
			],
		];
		$result  = json_decode( $this->response_body, true );

		if ( key_exists( 'code', $result ) && 401 === $result['code'] ) {
			update_option( 'wp_rocket_no_licence', true );
			update_rocket_option( 'remove_unused_css', 0 );
		}

		return wp_parse_args( (array) $result, $default );
	}

	/**
	 * Get job status from RUCSS queue.
	 *
	 * @param string $job_id Job ID.
	 * @param string $queue_name Queue Name.
	 * @param bool   $is_home Is home or not.
	 *
	 * @return array
	 */
	public function get_queue_job_status( $job_id, $queue_name, $is_home = false ) {
		$args = [
			'body'    => [
				'id'          => $job_id,
				'force_queue' => $queue_name,
				'is_home'     => $is_home,
			],
			'timeout' => 5,
		];

		if ( ! $this->handle_get( $args ) ) {
			return [
				'code'    => $this->response_code,
				'message' => $this->error_message,
			];
		}

		$default = [
			'code'     => 400,
			'status'   => 'failed',
			'message'  => 'No message. Defaulted in get_queue_job_status',
			'contents' => [
				'success'   => false,
				'shakedCSS' => '',
			],
		];

		$result = json_decode( $this->response_body, true );
		return (array) wp_parse_args( ( $result && $result['returnvalue'] ) ? (array) $result['returnvalue'] : [], $default );
	}
}
