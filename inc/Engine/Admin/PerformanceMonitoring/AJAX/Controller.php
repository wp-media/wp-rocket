<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\AJAX;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\{
	Render,
	PageHandlerTrait,
	GlobalScore,
	Jobs\Manager,
	Context\PerformanceMonitoringContext as Context,
	Database\Queries\PerformanceMonitoring as PMQuery
};
use WP_Rocket\Engine\License\API\User;

class Controller {

	use PageHandlerTrait;

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
	 * GlobalScore instance.
	 *
	 * @var GlobalScore
	 */
	private $global_score;

	/**
	 * Render instance.
	 *
	 * @var Render
	 */
	private $render;

	/**
	 * User client API instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Constructor.
	 *
	 * @param PMQuery     $query Query instance.
	 * @param Manager     $manager Manager instance.
	 * @param Context     $context Context instance.
	 * @param GlobalScore $global_score GlobalScore instance.
	 * @param Render      $render Render instance.
	 * @param User        $user User client API instance.
	 */
	public function __construct( PMQuery $query, Manager $manager, Context $context, GlobalScore $global_score, Render $render, User $user ) {
		$this->query        = $query;
		$this->manager      = $manager;
		$this->context      = $context;
		$this->global_score = $global_score;
		$this->render       = $render;
		$this->user         = $user;
	}

	/**
	 * Handles the AJAX request to add a new page URL for performance monitoring.
	 *
	 * @return void Outputs a JSON response and terminates execution.
	 */
	public function add_new_page(): void {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		// Check if adding a page is allowed based on URL limits.
		if ( ! wpm_apply_filters_typesafe( 'wpr_pm_allow_add_page', true ) ) {
			wp_send_json_error(
				[
					'error'   => true,
					'message' => __( 'Maximum number of URLs reached for your license.', 'rocket' ),
				]
				);
		}

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

		$payload['id']                = $row_id;
		$payload['html']              = $this->render->get_performance_monitoring_list_row( $row_data );
		$payload['global_score_data'] = $this->get_global_score_payload();
		$payload['remaining_urls']    = $this->get_remaining_url_count();

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

		$response = $this->get_page_content( $url );

		if ( ! $response ) {
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
		$payload['message'] = $response;

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
		if ( empty( $_GET['ids'] ) || ! is_array( $_GET['ids'] ) ) {
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
			'id__in' => $ids,
		];

		$results = $this->query->query( $query_params );

		// Result is empty.
		if ( empty( $results ) ) {
			$payload['results'] = 'No rows found in DB for ids: ' . implode( ',', $ids );
			wp_send_json_error( $payload );
		}

		foreach ( $results as $result ) {
			$result->html = $this->render->get_performance_monitoring_list_row( $result );
		}

		$payload['results']           = $results;
		$payload['global_score_data'] = $this->get_global_score_payload();

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

		$this->manager->add_url_to_the_queue(
			$row->url, // @phpstan-ignore-line
			true,
			[
				'data'       => '',
				'score'      => '',
				'report_url' => '',
				'is_blurred' => 0,
			]
			);

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
				'id'                => $id,
				'html'              => $this->render->get_performance_monitoring_list_row( $row ),
				'global_score_data' => $this->get_global_score_payload(),
				'remaining_urls'    => $this->get_remaining_url_count(),
			]
			);
	}

	/**
	 * Retrieves the global performance score payload for AJAX responses.
	 *
	 * Gets the global score data, determines the status color, and generates the HTML
	 * for the global score widget.
	 *
	 * @return array {
	 *     @type array  $data Global score data including score, pages_num, status, and status-color.
	 *     @type string $html Rendered HTML for the global score widget.
	 * }
	 */
	private function get_global_score_payload() {
		$payload = [];

		$payload                   = $this->global_score->get_global_score_data();
		$payload['status-color']   = $this->render->get_score_color_status( (int) $payload['score'] );
		$payload['remaining_urls'] = $this->get_remaining_url_count();

		return [
			'data'     => $payload,
			'html'     => $this->render->get_global_score_widget( $payload ),
			'row_html' => $this->render->get_global_score_row( $payload ),
		];
	}

	/**
	 * Get the remaining number of URLs that can be added based on user's plan limit.
	 *
	 * @return int Number of URLs that can still be added.
	 */
	private function get_remaining_url_count(): int {
		$current_url_count = $this->query->query( [ 'count' => true ] );
		$max_urls          = $this->user->get_pma_addon_limit( $this->user->get_pma_addon_sku_active() );

		return max( 0, $max_urls - (int) $current_url_count );
	}
}
