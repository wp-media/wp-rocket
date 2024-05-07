<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\JobManager\APIHandler;

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
	 * Array of Factories.
	 *
	 * @var array
	 */
	protected $factories;

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

		/**
		 * Filter the url that is sent to Saas when RUCSS and LCP/ATF is on.
		 *
		 * @param string $url contains the URL that is sent to Saas.
		 */
		$url = apply_filters( 'rocket_saas_api_queued_url', $url );

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
}
