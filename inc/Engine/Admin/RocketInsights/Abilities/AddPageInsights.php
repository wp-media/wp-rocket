<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Abilities;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Admin\RocketInsights\{
	Jobs\Manager,
	Context\Context,
	Database\Queries\RocketInsights as Query,
	Managers\Plan
};
use WP_Rocket\Engine\Common\Page\PageHandlerTrait;
use WP_Rocket\Engine\Common\{
	JobManager\JobProcessor,
	JobManager\Queue\Queue,
	Utils
};
use WP_Rocket\Engine\Tracking\TrackingTrait;
use WP_Rocket\Logger\Logger;

class AddPageInsights implements AbilitiesInterface {
	use PageHandlerTrait;
	use TrackingTrait;

	/**
	 * Context instance providing necessary dependencies and configuration.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Job manager responsible for handling Rocket Insights jobs.
	 *
	 * @var Manager
	 */
	private $manager;

	/**
	 * Class responsible for processing jobs and communicating with the API.
	 *
	 * @var JobProcessor
	 */
	private $job_processor;

	/**
	 * Queue system for scheduling and managing asynchronous jobs.
	 *
	 * @var Queue
	 */
	private $queue;

	/**
	 * Database query class for interacting with Rocket Insights data.
	 *
	 * @var Query
	 */
	private $query;

	/**
	 * Class responsible for retrieving information about the user's current plan and limits.
	 *
	 * @var Plan
	 */
	private $plan;

	/**
	 * Constructor.
	 *
	 * @param Context      $context       The context instance providing necessary dependencies and configuration.
	 * @param Manager      $manager       The job manager responsible for handling Rocket Insights jobs.
	 * @param JobProcessor $job_processor The class responsible for processing jobs and communicating with the API.
	 * @param Queue        $queue         The queue system for scheduling and managing asynchronous jobs.
	 * @param Query        $query         The database query class for interacting with Rocket Insights data.
	 * @param Plan         $plan          The class responsible for retrieving information about the user's current plan and limits.
	 */
	public function __construct( Context $context, Manager $manager, JobProcessor $job_processor, Queue $queue, Query $query, Plan $plan ) {
		$this->context       = $context;
		$this->manager       = $manager;
		$this->job_processor = $job_processor;
		$this->queue         = $queue;
		$this->query         = $query;
		$this->plan          = $plan;
	}

	/**
	 * Registers the ability to add page insights.
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/add-page-insights',
			[
				'label'               => __( 'Add Page Insights', 'rocket' ),
				'description'         => _x(
					'Adds a URL to Rocket Insights monitoring. Requires a valid URI.
Use this when the user wants to monitor a new page. Do not use it when the page is already monitored; use retest-page-insights instead.
Confirm the exact URL with the user before calling. This action is non-idempotent, so calling it twice for the same URL may return an error.
On success, tell the user the first score may take a few minutes and offer to trigger an immediate test with retest-page-insights Recheck every minute in the background and poll results with get-page-insights-score.',
					'Ability description',
					'rocket'
					),
				'category'            => 'wp-rocket-insights',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'url' => [
							'type'   => 'string',
							'format' => 'uri',
						],
					],
					'required'   => [ 'url' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [
							'type'        => 'boolean',
							'description' => __( 'Indicates whether the page was successfully added', 'rocket' ),
						],
						'error'   => [
							'type'        => 'string',
							'description' => __( 'Error message if the page could not be added', 'rocket' ),
						],
					],
				],
				'execute_callback'    => [ $this, 'execute' ],
				'permission_callback' => [ $this, 'check_permissions' ],
				'meta'                => [
					'mcp'          => [
						'public' => true,
					],
					'show_in_rest' => true,
					'annotations'  => [
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => false,
					],
				],
			]
		);
	}

	/**
	 * Checks if the current user has permission to execute this ability.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Executes the ability to add page insights.
	 *
	 * @param array $input The input data containing the URL to be added.
	 *
	 * @return array
	 */
	public function execute( $input = null ): array {
		$this->track_event( 'MCP Ability Executed', [ 'ability' => 'wp-rocket/add-page-insights' ] );
		$payload = $this->get_url_validation_payload( $input['url'] );

		if ( $payload['error'] ) {
			return [
				'success' => false,
				'error'   => $payload['message'],
			];
		}

		$url = $payload['processed_url'];

		if ( Utils::is_home( $url ) ) {
			$page_title = __( 'Homepage', 'rocket' );
		} else {
			$page_title = $this->get_page_title( $payload['message'] );
		}

		$source = 'mcp-ai';

		$additional_details = [
			'title' => $page_title,
			'data'  => [
				'source' => $source,
			],
		];

		// Handle synchronous submission using shared method.
		$row_id = $this->handle_sync_submission( $url, true, $additional_details );

		if ( empty( $row_id ) ) {
			return [
				'success' => false,
				'error'   => 'Failed to add page to monitoring queue.',
			];
		}

		// Check URL limit again after insertion to handle race conditions.
		// If the limit is exceeded, remove the newly added URL and return an error.
		if ( $this->query->get_total_count() > $this->plan->max_urls() ) {
			// Delete the newly added URL.
			$this->query->delete_item( $row_id );

			return [
				'success' => false,
				'error'   => 'URL limit exceeded.',
			];
		}

		$urls_count   = $this->query->get_total_count();
		$current_plan = $this->plan->get_current_plan();

		/**
		 * Fires when a performance monitoring job is added.
		 *
		 * @since 3.20
		 *
		 * @param string $url        The URL that was added for monitoring.
		 * @param string $plan       Plan name.
		 * @param int    $urls_count The current number of URLs being monitored.
		 * @param string $source     The source of the request.
		 */
		do_action( 'rocket_rocket_insights_job_added', $url, $current_plan, $urls_count, $source );

		return [
			'success' => true,
			'error'   => '',
		];
	}

	/**
	 * Validates a given URL for performance monitoring eligibility.
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
			'data'          => [
				'status' => 400,
			],
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
			$payload['message'] = 'URL has already been submitted for performance monitoring.';

			return $payload;
		}

		// Fetch url body and send to payload.
		$payload['message'] = $response;

		return $payload;
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
	 * @return bool|null Row ID on success, false on failure, null if not allowed.
	 */
	private function handle_sync_submission( string $url, bool $is_mobile, array $additional_details = [] ) {
		// Attempt synchronous API submission.
		$sync_response = $this->job_processor->send_api( $url, $is_mobile, 'rocket_insights', true );

		// If sync submission failed or returned WP_Error, fall back to async queue.
		if ( false === $sync_response || empty( $sync_response['uuid'] ) ) {
			Logger::error(
				'Rocket Insights: Synchronous Submission failed, Now falling back to Async Queue.',
				[
					'url' => $url,
				]
			);
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

		Logger::info(
			'Rocket Insights: Synchronous Submission successful, Now scheduling single job to run in 30 seconds.',
			[
				'url' => $url,
			]
		);

		// Update to in-progress status immediately with job_id.
		$this->manager->make_status_inprogress( $url, $is_mobile, 'rocket_insights', [ 'job_id' => $sync_response['uuid'] ] );
		$this->queue->schedule_job_status_single_task( time() + 30, $url, $is_mobile, 'rocket_insights' );

		return $row_id;
	}
}
