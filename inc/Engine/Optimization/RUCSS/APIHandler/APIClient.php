<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\APIHandler;

use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

class APIClient extends AbstractAPIClient implements LoggerAwareInterface {
	use LoggerAware;

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
		$url = add_query_arg(
			[
				'nowprocket'  => 1,
				'no_optimize' => 1,
			],
			user_trailingslashit( $url )
		);

		$url = $this->filter_url( $url, 'rucss' );

		$args = [
			'body'    => [
				'url'    => $url,
				'config' => $options,
			],
			'timeout' => 5,
		];

		$this->logger::debug(
			'Add to queue request arguments',
			$args
		);

		$sent = $this->handle_post( $args );

		if ( ! $sent ) {
			$output = [
				'code'    => $this->response_code,
				'message' => $this->error_message,
			];

			$this->logger::error(
				'Add to queue request failure',
				$output
			);
			return $output;
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

		$this->logger::debug(
			$url . ' - Add to queue response body',
			$result
		);

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
				'success'               => false,
				'shakedCSS'             => '',
				'above_the_fold_result' => [
					'lcp'               => [],
					'images_above_fold' => [],
				],
			],
		];

		$result = json_decode( $this->response_body, true );
		return (array) wp_parse_args( ( $result && $result['returnvalue'] ) ? (array) $result['returnvalue'] : [], $default );
	}

	/**
	 * Validate add to queue response.
	 *
	 * @param array $response Response array to be validated.
	 * @return bool
	 */
	public function validate_add_to_queue_response( array $response ): bool {
		return isset( $response['contents'], $response['contents']['jobId'], $response['contents']['queueName'] );
	}

	/**
	 * Handle SaaS request error.
	 *
	 * @param array|\WP_Error $response WP Remote request.
	 *
	 * @return bool
	 */
	protected function check_response( $response ): bool {
		$code = is_array( $response )
			? wp_remote_retrieve_response_code( $response )
			: (int) $response->get_error_code();

		if ( in_array( $code, [ 200, 201 ], true ) ) {
			delete_transient( 'wp_rocket_rucss_errors_count' );
			return parent::check_response( $response );
		}

		set_transient(
			'wp_rocket_rucss_errors_count',
			(int) get_transient( 'wp_rocket_rucss_errors_count' ) + 1,
			5 * MINUTE_IN_SECONDS
		);

		return parent::check_response( $response );
	}
}
