<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\AJAX;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\{
	Database\Queries\PerformanceMonitoring as PMQuery,
	Jobs\Manager,
	Context\PerformanceMonitoringContext as Context
};
use WP_Rocket\Abstract_Render;

class Controller extends Abstract_Render {
	/**
	 * Query object.
	 *
	 * @var PMQuery
	 */
	private $query;

	/**
	 * Manager instance.
	 *
	 * @var Manager
	 */
	private $manager;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param PMQuery $query Query instance.
	 * @param Manager $manager Manager instance.
	 * @param Context $context Context instance.
	 * @param string  $template_path Absolute path to the views/settings.
	 */
	public function __construct( PMQuery $query, Manager $manager, Context $context, $template_path ) {
		parent::__construct( $template_path );

		$this->query   = $query;
		$this->manager = $manager;
		$this->context = $context;
	}

	/**
	 * Handles the AJAX request to add a new page URL for performance monitoring.
	 *
	 * @return void Outputs a JSON response and terminates execution.
	 */
	public function add_new_page(): void {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		$url = isset( $_POST['page_url'] ) ? untrailingslashit( esc_url_raw( sanitize_text_field( wp_unslash( $_POST['page_url'] ) ) ) ) : '';

		$payload = $this->get_url_validation_payload( $url );

		if ( $payload['error'] ) {
			wp_send_json_error( $payload );
		}

		$row_id   = $this->manager->add_url_to_the_queue( $url, true );
		$row_data = $this->query->get_row_by_id( $row_id );

		$payload['id']   = $row_id;
		$payload['html'] = $this->generate( 'partials/performance-monitoring-row', $row_data );

		wp_send_json_success( $payload );
	}

	/**
	 * Validates a given URL for performance monitoring eligibility.
	 *
	 * @param string $url The URL to validate.
	 *
	 * @return array {
	 *     @type bool   $error   Whether an error occurred during validation.
	 *     @type string $message The error message, or an empty string if no error.
	 * }
	 */
	protected function get_url_validation_payload( string $url ): array {
		$payload = [
			'error'   => false,
			'message' => '',
		];

		if ( 'local' === wp_get_environment_type() ) {
			$payload['error']   = true;
			$payload['message'] = 'Performance monitoring is disabled for local environment';

			return $payload;
		}

		// Validate that performance monitoring is not disabled.
		if ( ! $this->context->is_allowed() ) {
			$payload['error']   = true;
			$payload['message'] = 'Performance monitoring is disabled.';

			return $payload;
		}
		// Validate that url is not empty.
		if ( '' === $url ) {
			$payload['error']   = true;
			$payload['message'] = 'No url provided.';

			return $payload;
		}

		// Check if url is an internal one.
		$url_parts         = get_rocket_parse_url( rocket_add_url_protocol( $url ) );
		$site_domain_parts = get_rocket_parse_url( home_url() );

		if ( $url_parts['host'] !== $site_domain_parts['host'] ) {
			$payload['error']   = true;
			$payload['message'] = 'Url is external.';

			return $payload;
		}

		// Check if url is a valid url.
		$response = wp_remote_head( $url );

		if ( is_wp_error( $response ) ) {
			$payload['error']   = true;
			$payload['message'] = 'Url does not resolve to a valid page.';

			return $payload;
		}

		// check if url is not from admin.
		if ( strpos( $url, admin_url() ) === 0 ) {
			$payload['error']   = true;
			$payload['message'] = 'Url is an admin page.';

			return $payload;
		}

		// Check if url has not been submited.
		if ( false !== $this->manager->get_single_job( $url, true ) ) {
			$payload['error']   = true;
			$payload['message'] = 'Page url performance is already been monitored.';

			return $payload;
		}

		// TODO: Check if url is not excluded.

		// TODO: Check if page is cached.

		return $payload;
	}

	/**
	 * Handles AJAX request to retrieve performance monitoring results for given IDs.
	 *
	 * @return void Outputs JSON response and terminates execution.
	 */
	public function get_results(): void {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		$payload = [];

		// Check if ids is set.
		if ( ! isset( $_GET['ids'] ) && ! is_array( $_GET['ids'] ) ) {
			$payload['results'] = 'No ids param available or ids not array';
			wp_send_json_error( $payload );
		}

		// Ensure everything is treated as integer.
		$ids = array_map( 'intval', $_GET['ids'] );

		// Remove anything that is not a valid integer > 0.
		$ids = array_filter( $ids );

		// Keep index clean.
		$ids = array_values( $ids );

		if ( empty( $ids ) ) {
			$payload['results'] = 'ids empty';
			wp_send_json_error( $payload );
		}

		$query_params = [
			'fields' => [
				'id',
				'score',
				'status',
				'modified',
			],
			'id__in' => $ids,
		];

		$results = $this->query->query( $query_params );

		// Result is empty.
		if ( empty( $results ) ) {
			$payload['results'] = 'No rows found in DB for ids: ' . implode( ',', $ids );
			wp_send_json_error( $payload );
		}

		$payload['results'] = $results;
		wp_send_json_success( $payload );
	}

	/**
	 * Reset testing a page using its ID.
	 *
	 * @return void
	 */
	public function reset_page() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		$id = ! empty( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		if ( empty( $id ) ) {
			wp_send_json_error(
				[
					'error'   => true,
					'message' => __( 'No ID was provided.', 'rocket' ),
				]
				);
		}

		$row = $this->query->get_row_by_id( $id );
		if ( ! $row ) {
			wp_send_json_error(
				[
					'error'   => true,
					'message' => __( 'Not valid ID', 'rocket' ),
				]
				);
		}

		$this->manager->add_url_to_the_queue( $row->url, true ); // @phpstan-ignore-line

		$row = $this->query->get_row_by_id( $id );
		wp_send_json_success(
			[
				'id'   => $id,
				'html' => $this->generate( 'partials/performance-monitoring-row', $row ),
			]
			);
	}
}
