<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Error;

/**
 * Recommendations API Client.
 *
 * Handles communication with the SaaS Director API for fetching performance recommendations.
 */
class APIClient extends AbstractAPIClient implements LoggerAwareInterface {
	use LoggerAware;

	/**
	 * SaaS Director API path for recommendations.
	 *
	 * @var string
	 */
	protected $request_path = 'recommendations/';

	/**
	 * Fetch recommendations from the SaaS API.
	 *
	 * @param array $params {
	 *     Query parameters for the recommendations API.
	 *
	 *     @type string      $email           User's email for historical tracking (required).
	 *     @type float|null  $lcp             Largest Contentful Paint in seconds.
	 *     @type float|null  $ttfb            Time to First Byte in seconds.
	 *     @type float|null  $cls             Cumulative Layout Shift.
	 *     @type int|null    $tbt             Total Blocking Time in milliseconds.
	 *     @type int|null    $global_score    Overall performance score (0-100).
	 *     @type array|null  $enabled_options List of enabled WP Rocket options.
	 *     @type string|null $language        ISO language code (e.g., "en", "fr").
	 *     @type int|null    $limit           Maximum number of recommendations (1-20).
	 *     @type string|null $version         WP Rocket version (e.g., "3.20.4").
	 * }
	 * @param array $args Additional request arguments (timeout, headers, etc.).
	 * @return array|WP_Error Response data or error array.
	 */
	public function get_recommendations( array $params, array $args = [] ) {
		// Validate required parameter.
		if ( empty( $params['email'] ) ) {
			$this->logger::error(
				'Recommendations API: Missing required email parameter'
			);

			return new WP_Error(
				'missing_email',
				'Email parameter is required for recommendations API.'
			);
		}

		// Build query parameters (remove null/empty values).
		$query_params = $this->build_query_params( $params );

		// Merge custom args with defaults.
		$args = array_merge(
			[
				'body'    => $query_params,
				'timeout' => 15,
			],
			$args
		);

		$this->logger::debug(
			'Recommendations API: Fetching recommendations',
			[
				'params' => $query_params,
			]
		);

		// Use AbstractAPIClient's handle_get method.
		$sent = $this->handle_get( $args );

		if ( ! $sent ) {
			$this->logger::error(
				'Recommendations API: Request failed',
				[
					'code'    => $this->response_code,
					'message' => $this->error_message,
				]
			);

			return new WP_Error(
				'api_request_failed',
				$this->error_message,
				[ 'status' => $this->response_code ]
			);
		}

		// Decode JSON response.
		$response_data = json_decode( $this->response_body, true );

		// Check for JSON decode error.
		if ( JSON_ERROR_NONE !== json_last_error() || null === $response_data ) {
			$this->logger::error( 'Recommendations API: Invalid JSON response' );
			return new WP_Error(
				'invalid_json',
				'Invalid API response - malformed JSON'
			);
		}

		// Validate response structure.
		if ( ! $this->validate_response( $response_data ) ) {
			$this->logger::error(
				'Recommendations API: Invalid response structure',
				[ 'response' => $response_data ]
			);

			return new WP_Error(
				'invalid_structure',
				'Invalid API response structure'
			);
		}

		$this->logger::info(
			'Recommendations API: Recommendations fetched successfully',
			[
				'total_recommendations' => count( $response_data['recommendations'] ?? [] ),
				'language'              => $response_data['metadata']['language'] ?? 'unknown',
			]
		);

		return [
			'code' => $this->response_code,
			'data' => $response_data,
		];
	}

	/**
	 * Build query parameters array, removing null and empty values.
	 *
	 * @param array $params Raw parameters.
	 * @return array Filtered parameters.
	 */
	private function build_query_params( array $params ): array {
		// Map of allowed parameters.
		$allowed_params = [
			'email',
			'lcp',
			'ttfb',
			'cls',
			'tbt',
			'global_score',
			'enabled_options',
			'language',
			'limit',
			'version',
		];

		$query_params = [];

		foreach ( $allowed_params as $key ) {
			if ( isset( $params[ $key ] ) && '' !== $params[ $key ] ) {
				$query_params[ $key ] = $params[ $key ];
			}
		}

		return $query_params;
	}

	/**
	 * Validate response structure.
	 *
	 * @param array $response Response data.
	 * @return bool True if valid, false otherwise.
	 */
	public function validate_response( array $response ): bool {
		if ( ! isset( $response['recommendations'] ) || ! is_array( $response['recommendations'] ) ) {
			return false;
		}

		// Must have 'metadata' key (array).
		if ( ! isset( $response['metadata'] ) || ! is_array( $response['metadata'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Force this value to be true for the Job Manager to consider the request successful because this APIClient won't be used in queue.
	 *
	 * @param array $response Response data.
	 * @return bool
	 */
	public function validate_add_to_queue_response( array $response ): bool {
		return true;
	}
}
