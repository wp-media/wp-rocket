<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

/**
 * Recommendations API Client.
 *
 * Handles communication with the SaaS Director API for fetching performance recommendations.
 *
 * @since 3.20.5
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
	 *     @type string|null $enabled_options Comma-separated list of enabled WP Rocket options.
	 *     @type string|null $language        ISO language code (e.g., "en", "fr").
	 *     @type int|null    $limit           Maximum number of recommendations (1-20).
	 *     @type string|null $version         WP Rocket version (e.g., "3.20.4").
	 * }
	 * @param array $args Additional request arguments (timeout, headers, etc.).
	 * @return array|\WP_Error Response data or error array.
	 */
	public function get_recommendations( array $params, array $args = [] ) {
		// Validate required parameter
		if ( empty( $params['email'] ) ) {
			$error_data = [
				'code'    => 400,
				'message' => 'Email parameter is required for recommendations API.',
			];

			$this->logger::error(
				'Recommendations API: Missing required email parameter',
				$error_data
			);

			return $error_data;
		}

		// Build query parameters (remove null/empty values)
		$query_params = $this->build_query_params( $params );

		// Merge custom args with defaults
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

		// Use AbstractAPIClient's handle_get method
		$sent = $this->handle_get( $args );

		if ( ! $sent ) {
			$error_data = [
				'code'    => $this->response_code,
				'message' => $this->error_message,
			];

			$this->logger::error(
				'Recommendations API: Request failed',
				$error_data
			);

			return $error_data;
		}

		// Decode JSON response
		$response_data = json_decode( $this->response_body, true );

		// Check for JSON decode error
		if ( ! $response_data ) {
			$error_data = [
				'code'    => 400,
				'message' => 'Invalid API response - malformed JSON',
			];

			$this->logger::error(
				'Recommendations API: Invalid JSON response',
				$error_data
			);

			return $error_data;
		}

		// Validate response structure
		if ( ! $this->validate_response( $response_data ) ) {
			$error_data = [
				'code'    => 400,
				'message' => 'Invalid API response structure',
			];

			$this->logger::error(
				'Recommendations API: Invalid response structure',
				array_merge( $error_data, [ 'response' => $response_data ] )
			);

			return $error_data;
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
		// Map of allowed parameters
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
			if ( isset( $params[ $key ] ) && null !== $params[ $key ] && '' !== $params[ $key ] ) {
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
		// Must have 'recommendations' key (array)
		if ( ! isset( $response['recommendations'] ) || ! is_array( $response['recommendations'] ) ) {
			return false;
		}

		// Must have 'metadata' key (array)
		if ( ! isset( $response['metadata'] ) || ! is_array( $response['metadata'] ) ) {
			return false;
		}

		return true;
	}

	public function validate_add_to_queue_response(array $response): bool {
		return true;
	}
}
