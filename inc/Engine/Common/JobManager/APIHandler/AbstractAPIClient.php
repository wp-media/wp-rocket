<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\JobManager\APIHandler;

use WP_Error;
use WP_Rocket\Admin\Options_Data;

abstract class AbstractAPIClient {
	/**
	 * API URL.
	 */
	const API_URL = 'https://saas.wp-rocket.me/';

	/**
	 * SaaS "Feature Disabled" contract.
	 *
	 * While the SaaS kill switch is on, new submissions are answered with a body whose `error` is
	 * "Feature Disabled", a `message`, and a `Retry-After` header in integer seconds:
	 * - POST rucss-job: HTTP 200, body `{"code":403,"error":"Feature Disabled","message":"RUCSS job submission is temporarily disabled. Please retry later."}`
	 * - POST performance/: HTTP 503, body `{"code":503,"error":"Feature Disabled","message":"Rocket Insights submissions are temporarily disabled. Please retry later."}`
	 *
	 * Detection only relies on the `error` marker, so a later move of RUCSS to a real HTTP 503 is still matched.
	 * Only integer-seconds `Retry-After` is supported (HTTP-date is not); other values use the default delay.
	 * Delays are clamped between MIN and MAX; CLOCK_SLACK tolerates clock drift between web nodes.
	 */
	const FEATURE_DISABLED_ERROR         = 'Feature Disabled';
	const FEATURE_DISABLED_DEFAULT_DELAY = 900;
	const FEATURE_DISABLED_MIN_DELAY     = 60;
	const FEATURE_DISABLED_MAX_DELAY     = 3600;
	const FEATURE_DISABLED_CLOCK_SLACK   = 60;

	/**
	 * Part of request Url after the main API_URL.
	 *
	 * @var string
	 */
	protected $request_path;

	/**
	 * Response Code.
	 *
	 * @var int
	 */
	protected $response_code = 200;

	/**
	 * Error message.
	 *
	 * @var string
	 */
	protected $error_message = '';

	/**
	 * Response Body.
	 *
	 * @var string
	 */
	protected $response_body;

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Handle the request.
	 *
	 * @param array  $args Passed arguments.
	 * @param string $type GET or POST.
	 *
	 * @return bool
	 */
	private function handle_request( array $args, string $type = 'post' ) {
		$api_url = rocket_get_constant( 'WP_ROCKET_SAAS_API_URL', false )
			? rocket_get_constant( 'WP_ROCKET_SAAS_API_URL', false )
			: self::API_URL;

		$is_post = 'post' === $type;

		if ( $is_post && $this->is_feature_disabled() ) {
			$this->set_feature_disabled_result();
			return false;
		}

		if ( empty( $args['body'] ) ) {
			$args['body'] = [];
		}

		$args['body']['credentials'] = [
			'wpr_email' => $this->options->get( 'consumer_email', '' ),
			'wpr_key'   => $this->options->get( 'consumer_key', '' ),
		];

		$args['method'] = strtoupper( $type );

		if ( ! empty( $args['json_encode'] ) ) {
			unset( $args['json_encode'] );
			$args['body'] = wp_json_encode( $args['body'] );
		}

		$response = wp_remote_request(
			$api_url . $this->request_path,
			$args
		);

		if ( $is_post && $this->maybe_set_feature_disabled( $response ) ) {
			$this->set_feature_disabled_result();
			return false;
		}

		return $this->check_response( $response );
	}

	/**
	 * Transient key storing the end of the "Feature Disabled" back-off. Empty disables the feature.
	 *
	 * @return string
	 */
	protected function get_feature_disabled_transient_key(): string {
		return '';
	}

	/**
	 * Whether the SaaS feature behind this client is currently backed off.
	 *
	 * @return bool
	 */
	public function is_feature_disabled(): bool {
		$key = $this->get_feature_disabled_transient_key();

		if ( '' === $key ) {
			return false;
		}

		$until = (int) get_transient( $key );
		$now   = time();

		if ( $until <= $now ) {
			return false;
		}

		// A value beyond the maximum delay is invalid: fail open, a new response sets the block again.
		if ( $until - $now > self::FEATURE_DISABLED_MAX_DELAY + self::FEATURE_DISABLED_CLOCK_SLACK ) {
			delete_transient( $key );
			return false;
		}

		return true;
	}

	/**
	 * Set the result exposed to callers when a submission is not sent or is refused as disabled.
	 *
	 * @return void
	 */
	private function set_feature_disabled_result() {
		$this->response_code = 503;
		$this->error_message = self::FEATURE_DISABLED_ERROR;
	}

	/**
	 * Get the back-off delay in seconds from the Retry-After header.
	 *
	 * @param array $response WP Remote response.
	 *
	 * @return int
	 */
	private function get_retry_after_delay( array $response ): int {
		$header = wp_remote_retrieve_header( $response, 'retry-after' );

		if ( is_array( $header ) ) {
			$header = reset( $header );
		}

		$header  = trim( (string) $header );
		$seconds = ( '' !== $header && ctype_digit( $header ) ) ? (int) $header : self::FEATURE_DISABLED_DEFAULT_DELAY;

		return max( self::FEATURE_DISABLED_MIN_DELAY, min( self::FEATURE_DISABLED_MAX_DELAY, $seconds ) );
	}

	/**
	 * Store the back-off when the response is a "Feature Disabled" one.
	 *
	 * @param array|WP_Error $response WP Remote response.
	 *
	 * @return bool True when the back-off was set.
	 */
	private function maybe_set_feature_disabled( $response ): bool {
		$key = $this->get_feature_disabled_transient_key();

		if ( '' === $key || ! is_array( $response ) ) {
			return false;
		}

		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) || ! isset( $body['error'] ) || self::FEATURE_DISABLED_ERROR !== $body['error'] ) {
			return false;
		}

		$delay = $this->get_retry_after_delay( $response );

		set_transient( $key, time() + $delay, $delay );

		return true;
	}

	/**
	 * Handle remote POST.
	 *
	 * @param array $args Array with options sent to Saas API.
	 *
	 * @return bool WP Remote request status.
	 */
	protected function handle_post( array $args ): bool {
		return $this->handle_request( $args );
	}

	/**
	 * Handle remote GET.
	 *
	 * @param array $args Array with options sent to Saas API.
	 *
	 * @return bool WP Remote request status.
	 */
	protected function handle_get( array $args ): bool {
		return $this->handle_request( $args, 'get' );
	}

	/**
	 * Handle SaaS request error.
	 *
	 * @param array|WP_Error $response WP Remote request.
	 *
	 * @return bool
	 */
	protected function check_response( $response ): bool {
		$this->response_code = is_array( $response )
			? wp_remote_retrieve_response_code( $response )
			: $response->get_error_code();

		if ( ! in_array( (int) $this->response_code, [ 200, 201 ], true ) ) {
			if ( empty( $response ) ) {
				$this->error_message = 'API Client Error';
				return false;
			}

			$this->error_message = is_array( $response )
				? wp_remote_retrieve_response_message( $response )
				: $response->get_error_message();

			return false;
		}

		$this->response_body = wp_remote_retrieve_body( $response );

		return true;
	}

	/**
	 * Validate add to queue response.
	 *
	 * @param array $response Response array to be validated.
	 * @return bool
	 */
	abstract public function validate_add_to_queue_response( array $response ): bool;

	/**
	 * Add a filter on url.
	 *
	 * @param string $url Url to be filtered.
	 * @param string $optimization_type Optimization type.
	 *
	 * @return mixed
	 */
	protected function filter_url( $url, $optimization_type ) {
		/**
		 * Filter the url that is sent to Saas.
		 *
		 * @param string $url contains the URL that is sent to Saas.
		 * @param string $optimization_type Optimization type.
		 */
		return wpm_apply_filters_typed( 'string', 'rocket_saas_api_queued_url', $url, $optimization_type );
	}
}
