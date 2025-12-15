<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\APIHandler;

use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

/**
 * Rocket Insights API Client
 *
 * Handles communication with the SaaS Director API for performance testing
 */
class GlobalScoreSaaSAPIClient extends AbstractAPIClient implements LoggerAwareInterface {
	use LoggerAware;

	/**
	 * SaaS Director API path for sending global score.
	 *
	 * @var string
	 */
	protected $request_path = 'rocket-insights-global-score/';

	/**
	 * Send the global score to SaaS.
	 *
	 * @param array $data Request body data.
	 * @param array $args Additional request arguments (timeout, headers, etc.).
	 * @return array|\WP_Error
	 */
	public function send_to_saas( array $data = [], array $args = [] ) {
		$request_body = wp_parse_args(
			$data,
			[
				'domain'         => home_url(),
				'average_score'  => 0,
				'blurred'        => 0,
				'credits_left'   => 0,
				'license'        => 'free',
				'automatic_test' => 0,
				'wpr_user_id'    => 0,
				'email'          => $this->options->get( 'consumer_email', '' ),
				'key'            => $this->options->get( 'consumer_key', '' ),
			]
			);

		$args = array_merge(
			[
				'json_encode' => true,
				'body'        => $request_body,
				'headers'     => [
					'Content-Type' => 'application/json',
				],
			],
			$args
		);

		$this->logger::debug(
			'Global Score SaaS: API send request',
			[
				'domain'        => $request_body['domain'],
				'average_score' => $request_body['average_score'],
			]
		);

		$sent = $this->handle_post( $args );

		if ( ! $sent ) {
			$error_data = [
				'code'    => $this->response_code,
				'message' => $this->error_message,
			];

			$this->logger::error(
				'Global Score SaaS: API failed',
				$error_data
			);

			return $error_data;
		}

		$response_data = json_decode( $this->response_body, true );

		$this->logger::info( 'Global Score SaaS: Request sent successfully' );

		$response_data['code'] = $this->response_code;

		return $response_data;
	}

	/**
	 * Validate add to queue response if it's valid or not.
	 *
	 * @param array $response Response array.
	 * @return bool
	 */
	public function validate_add_to_queue_response( array $response ): bool {
		return false;
	}
}
