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

		$page_title = $this->get_page_title( $payload['message'] );

		$row_id = $this->manager->add_url_to_the_queue(
			$url,
			true,
			[
				'title' => $page_title,
			]
			);

		if ( empty( $row_id ) ) {
			wp_send_json_error(
				[
					'error'   => true,
					'message' => esc_html__( 'Not valid inputs', 'rocket' ),
				]
				);
		}

		/**
		 * Fires when a performance monitoring job is added via AJAX.
		 *
		 * @since 3.20
		 *
		 * @param string $url The URL that was added for monitoring.b.
		 */
		do_action( 'rocket_pm_job_added', $url );

		$row_data = $this->query->get_row_by_id( (int) $row_id );

		// Remove message from the response payload.
		unset( $payload['message'] );

		$payload['id']   = $row_id;
		$payload['html'] = $this->generate( 'partials/performance-monitoring-row', $row_data );

		wp_send_json_success( $payload );
	}

	/**
	 * Extracts and sanitizes the page title from the provided HTML string.
	 *
	 * This method attempts to find the <title> tag in the given HTML, decodes any HTML entities,
	 * strips all tags, sanitizes the text, and then trims the title at common separators
	 * (such as " | ", " - ", " – ", " » ") to return a clean, concise page title.
	 *
	 * @param string $html The HTML content from which to extract the page title.
	 *
	 * @return string The sanitized and trimmed page title, or an empty string if not found.
	 */
	public function get_page_title( string $html ): string {
		$title = '';

		if ( empty( $html ) ) {
			return $title;
		}

		// Extract title from title tag.
		if ( ! preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $matches ) ) {
			return $title;
		}

		// Clean up and sanitize the title.
		$title = html_entity_decode( trim( $matches[1] ), ENT_QUOTES, 'UTF-8' );

		if ( empty( $title ) ) {
			return $title;
		}

		$title = wp_strip_all_tags( $title );
		$title = sanitize_text_field( $title );

		return $title;
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
		$user_agent = 'WP Rocket/Fetch Page Buffer for Performance Monitoring Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';
		$args       = [
			'user-agent' => $user_agent,
			'timeout'    => 60,
		];

		$response = wp_safe_remote_get( $url, $args );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
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

		// Fetch url body and send to payload.
		$payload['message'] = wp_remote_retrieve_body( $response );

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

		/**
		 * Fires when a performance monitoring job is reset/retested.
		 *
		 * @since 3.20
		 *
		 * @param int    $id The database row ID of the reset job.
		 */
		do_action( 'rocket_pm_job_retest', $id );

		$row = $this->query->get_row_by_id( $id );
		wp_send_json_success(
			[
				'id'   => $id,
				'html' => $this->generate( 'partials/performance-monitoring-row', $row ),
			]
			);
	}
}
