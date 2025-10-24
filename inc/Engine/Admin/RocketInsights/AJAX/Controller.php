<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\AJAX;

use WP_Rocket\Engine\Admin\RocketInsights\{
	Render,
	PageHandlerTrait,
	GlobalScore,
	Jobs\Manager,
	Context\Context,
	Database\Queries\RocketInsights as RIQuery,
	Managers\Plan
};
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Logger\Logger;

class Controller {
	use PageHandlerTrait;

	/**
	 * Query object.
	 *
	 * @var RIQuery
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
	 * Plan instance.
	 *
	 * @var Plan
	 */
	private $plan;

	/**
	 * JobProcessor instance.
	 *
	 * @var JobProcessor
	 */
	private $job_processor;

	/**
	 * Constructor.
	 *
	 * @param RIQuery      $query Query instance.
	 * @param Manager      $manager Manager instance.
	 * @param Context      $context Context instance.
	 * @param GlobalScore  $global_score GlobalScore instance.
	 * @param Render       $render Render instance.
	 * @param Plan         $plan Plan instance.
	 * @param JobProcessor $job_processor JobProcessor instance.
	 */
	public function __construct( RIQuery $query, Manager $manager, Context $context, GlobalScore $global_score, Render $render, Plan $plan, JobProcessor $job_processor ) {
		$this->query         = $query;
		$this->manager       = $manager;
		$this->context       = $context;
		$this->global_score  = $global_score;
		$this->render        = $render;
		$this->plan          = $plan;
		$this->job_processor = $job_processor;
	}

	/**
	 * Handles the AJAX request to add a new page URL for performance monitoring.
	 *
	 * @return void Outputs a JSON response and terminates execution.
	 */
	public function add_new_page(): void {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		// Check if adding a page is allowed based on URL limits.
		if ( ! $this->context->is_adding_page_allowed() ) {
			wp_send_json_error(
				[
					'error'          => true,
					'message'        => __( 'Maximum number of URLs reached for your license.', 'rocket' ),
					'remaining_urls' => 0,
					'can_add_pages'  => false,
				]
				);
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- URL validation happens in get_url_validation_payload(). Using trim() and wp_unslash() only to preserve percent-encoded non-ASCII characters.
		$url = isset( $_POST['page_url'] ) ? untrailingslashit( trim( wp_unslash( $_POST['page_url'] ) ) ) : '';

		// Validate URL format before further processing, but allow get_url_validation_payload to handle empty URLs.
		if ( ! empty( $url ) ) {
			// Add protocol if missing for validation, but preserve original input.
			$url_for_validation = rocket_add_url_protocol( $url );
			if ( ! wp_http_validate_url( $url_for_validation ) ) {
				wp_send_json_error(
					[
						'error'   => true,
						'message' => __( 'Invalid URL format.', 'rocket' ),
					]
				);
			}
		}

		$payload = $this->get_url_validation_payload( $url );

		if ( $payload['error'] ) {
			wp_send_json_error( $payload );
		}

		$url = $payload['processed_url'];

		if ( Utils::is_home( $url ) ) {
			$page_title = __( 'Homepage', 'rocket' );
		} else {
			$page_title = $this->get_page_title( $payload['message'] );
		}
		$additional_details = [
			'title' => $page_title,
		];

		// Handle synchronous submission using shared method.
		$row_id = $this->handle_sync_submission( $url, true, $additional_details );

		if ( empty( $row_id ) ) {
			wp_send_json_error(
				[
					'error'   => true,
					'message' => esc_html__( 'Not valid inputs', 'rocket' ),
				]
				);
		}

		$urls_count   = $this->query->get_total_count();
		$current_plan = $this->plan->get_current_plan();

		/**
		 * Fires when a Rocket Insights job is added via AJAX.
		 *
		 * @since 3.20
		 *
		 * @param string $url        The URL that was added for monitoring.
		 * @param string $plan       Plan name.
		 * @param int    $urls_count The current number of URLs being monitored.
		 */
		do_action( 'rocket_rocket_insights_job_added', $url, $current_plan, $urls_count );

		$row_data = $this->query->get_row_by_id( (int) $row_id );

		// Remove message from the response payload.
		unset( $payload['message'] );

		$payload['id']                = $row_id;
		$payload['html']              = $this->render->get_performance_monitoring_list_row( $row_data );
		$payload['global_score_data'] = $this->get_global_score_payload();
		$payload['remaining_urls']    = $this->get_remaining_url_count();
		$payload['has_credit']        = $this->plan->has_credit();
		$payload['can_add_pages']     = $this->context->is_adding_page_allowed();

		// Add disabled button html data to payload.
		if ( 0 === $this->get_remaining_url_count() ) {
			$data                  = $payload['global_score_data']['data'];
			$data['reach_max_url'] = true;

			$payload['global_score_data']['disabled_btn_html'] = [
				'global_score_widget' => $this->render->get_add_page_btn( 'global-score-widget', $data ),
				'rocket_insights'     => $this->render->get_add_page_btn( 'rocket-insights', $data ),
			];
		}

		wp_send_json_success( $payload );
	}

	/**
	 * Validates a given URL for Rocket Insights eligibility.
	 *
	 * @param string $url The URL to validate.
	 *
	 * @return array {
	 *     @type bool   $error        Whether an error occurred during validation.
	 *     @type string $message      The error message, or an empty string if no error.
	 *     @type string $processed_url The URL with protocol added if validation passes.
	 * }
	 */
	protected function get_url_validation_payload( string $url ): array {
		$payload = [
			'error'         => false,
			'message'       => '',
			'processed_url' => '',
		];

		if ( 'local' === wp_get_environment_type() ) {
			$payload['error']   = true;
			$payload['message'] = 'Rocket Insights is disabled for local environment';

			return $payload;
		}

		// Validate that Rocket Insights is not disabled.
		if ( ! $this->context->is_allowed() ) {
			$payload['error']   = true;
			$payload['message'] = 'Rocket Insights is disabled.';

			return $payload;
		}
		// Validate that url is not empty.
		if ( '' === $url ) {
			$payload['error']   = true;
			$payload['message'] = 'No url provided.';

			return $payload;
		}

		// Check if URL has protocol, add if needed.
		$url                      = rocket_add_url_protocol( $url );
		$payload['processed_url'] = $url;

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

		// Fetch url body and send to payload.
		$payload['message'] = $response;

		return $payload;
	}

	/**
	 * Handles AJAX request to retrieve Rocket Insights results for given IDs.
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
		$payload['has_credit']        = $this->plan->has_credit();
		$payload['can_add_pages']     = $this->context->is_adding_page_allowed();

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

		$additional_details = [
			'data'       => [
				'is_retest' => true,
			],
			'score'      => '',
			'report_url' => '',
			'is_blurred' => 0,
		];

		// Handle synchronous submission using shared method.
		$row_id = $this->handle_sync_submission( $row->url, true, $additional_details ); // @phpstan-ignore-line

		if ( empty( $row_id ) ) {
			wp_send_json_error(
				[
					'message' => __( 'Unable to reset performance test. Please try again.', 'rocket' ),
				]
			);
		}

		/**
		 * Fires when a Rocket Insights job is reset/retested.
		 *
		 * @since 3.20
		 *
		 * @param int    $id The database row ID of the reset job.
		 */
		do_action( 'rocket_rocket_insights_job_retest', $id );

		$row = $this->query->get_row_by_id( $id );
		wp_send_json_success(
			[
				'id'                => $id,
				'html'              => $this->render->get_performance_monitoring_list_row( $row ),
				'global_score_data' => $this->get_global_score_payload(),
				'remaining_urls'    => $this->get_remaining_url_count(),
				'has_credit'        => $this->plan->has_credit(),
				'can_add_pages'     => $this->context->is_adding_page_allowed(),
			]
			);
	}

	/**
	 * Handle synchronous submission of Rocket Insights job.
	 *
	 * This method centralizes the logic for attempting synchronous job submission
	 * and falling back to async queuing when needed. It uses JobProcessor's send_api
	 * for the actual API call, then adds Rocket Insights-specific validation and logging.
	 *
	 * @since 3.20
	 *
	 * @param string $url               The URL to test.
	 * @param bool   $is_mobile         Whether this is a mobile test.
	 * @param array  $additional_details Optional additional data to store with the job.
	 *
	 * @return int|false Row ID on success, false on failure.
	 */
	private function handle_sync_submission( string $url, bool $is_mobile, array $additional_details = [] ) {
		// Attempt synchronous API submission.
		$sync_response = $this->job_processor->send_api( $url, $is_mobile, 'rocket_insights', true );

		// If sync submission failed or returned WP_Error, fall back to async queue.
		if ( is_wp_error( $sync_response ) || empty( $sync_response['uuid'] ) ) {
			return $this->manager->add_to_the_queue( $url, $is_mobile, $additional_details );
		}

		// Success! Save with the new data.
		$row_id = $this->manager->add_to_the_queue( $url, $is_mobile, $additional_details );

		if ( empty( $row_id ) ) {
			// DB insert failed after successful API submission - log orphaned job.
			Logger::error(
				'Rocket Insights: Database insert failed after successful sync submission',
				[
					'url'    => $url,
					'job_id' => $sync_response['uuid'],
				]
			);
			return false;
		}

		// Update to pending status immediately with job ID.
		$this->query->make_status_pending( $url, $sync_response['uuid'], '', $is_mobile );

		return $row_id;
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
		$payload                   = $this->global_score->get_global_score_data();
		$payload['status-color']   = $this->render->get_score_color_status( (int) $payload['score'] );
		$payload['remaining_urls'] = $this->get_remaining_url_count();

		return [
			'data'     => $payload,
			'html'     => $this->render->get_global_score_widget_content( $payload ),
			'row_html' => $this->render->get_global_score_row( $payload ),
		];
	}

	/**
	 * Get the remaining number of URLs that can be added based on user's plan limit.
	 *
	 * @return int Number of URLs that can still be added.
	 */
	private function get_remaining_url_count(): int {
		return max(
			0,
			$this->plan->max_urls() - (int) $this->query->
			get_total_count()
		);
	}
}
