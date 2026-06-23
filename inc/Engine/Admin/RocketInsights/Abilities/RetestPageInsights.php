<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Abilities;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Admin\RocketInsights\{
	Jobs\Manager,
	Context\Context
};
use WP_Rocket\Engine\Common\{
	JobManager\JobProcessor,
	JobManager\Queue\Queue
};
use WP_Rocket\Engine\Tracking\TrackingTrait;
use WP_Rocket\Logger\Logger;

class RetestPageInsights implements AbilitiesInterface {
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
	 * Constructor.
	 *
	 * @param Context      $context       The context instance providing necessary dependencies and configuration.
	 * @param Manager      $manager       The job manager responsible for handling Rocket Insights jobs.
	 * @param JobProcessor $job_processor The class responsible for processing jobs and communicating with the API.
	 * @param Queue        $queue         The queue system for scheduling and managing asynchronous jobs.
	 */
	public function __construct( Context $context, Manager $manager, JobProcessor $job_processor, Queue $queue ) {
		$this->context       = $context;
		$this->manager       = $manager;
		$this->job_processor = $job_processor;
		$this->queue         = $queue;
	}

	/**
	 * Registers the ability to retest page insights.
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/retest-page-insights',
			[
				'label'               => __( 'Retest Page Insights', 'rocket' ),
				'description'         => __(
					'Triggers a fresh Rocket Insights performance test for an already monitored URL. Requires a valid URI.
Use this when the user wants a fresh test for a monitored page. Do not call it while a test is already running.
Handle response statuses as follows: triggered means the test was queued and results should be polled with get-page-insights-score; running means a test is already in progress and should not be triggered again; not_found means the page is not tracked and add-page-insights should be offered; failed means an error occurred.
If the user wants results, wait and check every minute with get-page-insights-score, since the test may take a few minutes.',
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
							'description' => __( 'Whether the retest was triggered', 'rocket' ),
						],
						'status'  => [
							'type'        => 'string',
							'enum'        => [ 'running', 'triggered', 'not_found', 'failed' ],
							'description' => __( 'Current job status: running = test already in flight; triggered = retest queued; not_found = URL not tracked; failed = error', 'rocket' ),
						],
						'error'   => [
							'type'        => 'string',
							'description' => __( 'Error message if retest could not be triggered', 'rocket' ),
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
	 * MCP OAuth gates only on login by design — the ability owns its permission check.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Executes the ability to retest page insights.
	 *
	 * @param array $input The input data containing the URL to be retested.
	 *
	 * @return array
	 */
	public function execute( $input = null ): array {
		$this->track_event( 'MCP Ability Executed', [ 'ability' => 'wp-rocket/retest-page-insights' ] );
		// Guard: local environments do not support performance monitoring.
		if ( 'local' === wp_get_environment_type() ) {
			return [
				'success' => false,
				'status'  => 'failed',
				'error'   => 'Performance monitoring is disabled for local environment',
			];
		}

		// Guard: performance monitoring may be disabled via context (e.g. plan, settings).
		if ( ! $this->context->is_allowed() ) {
			return [
				'success' => false,
				'status'  => 'failed',
				'error'   => 'Performance monitoring is disabled.',
			];
		}

		// Normalize URL — add protocol if missing.
		$url = rocket_add_url_protocol( $input['url'] );

		// Look up the existing DB row for this URL. get_single_job() returns bool|object.
		$row = $this->manager->get_single_job( $url, true );

		if ( ! is_object( $row ) ) {
			return [
				'success' => false,
				'status'  => 'not_found',
				'error'   => 'URL is not tracked by Rocket Insights.',
			];
		}

		// Guard: delegate running-state check to the Row object.
		// is_running() encapsulates the canonical set ['to-submit', 'pending', 'in-progress'].
		//
		// Note (concurrency race): there is a TOCTOU window between this is_running() check
		// and the reset_job() call inside add_url_to_the_queue(). A cron transition in that
		// window could clobber a newly in-flight job. This is accepted best-effort behavior —
		// Rest::update_item() has the identical race and ships without additional locking.
		if ( $row->is_running() ) {
			return [
				'success' => true,
				'status'  => 'running',
				'error'   => '',
			];
		}

		// Build submission details with stale-score reset fields.
		//
		// score, report_url, and is_blurred are passed through add_to_the_queue() →
		// add_url_to_the_queue() → reset_job(), which clears the previous result from the
		// DB row immediately. Without these fields the row retains the old score until the new
		// test completes, causing get-insights-scores to return stale data during the retest window.
		//
		// Note (stuck rows): a row permanently stuck in a running state (e.g. cron crashed after
		// submission) will cause is_running() to return true indefinitely, blocking retests. The
		// only recovery path is manual deletion from the admin UI. This is a pre-existing limitation
		// shared with Rest::update_item() and is out of scope for this PR.
		$additional_details = [
			'data'       => [
				'is_retest' => true,
				'source'    => 'mcp-ai',
			],
			'score'      => '',
			'report_url' => '',
			'is_blurred' => 0,
		];

		$row_id = $this->handle_sync_submission( $url, true, $additional_details );

		if ( empty( $row_id ) ) {
			return [
				'success' => false,
				'status'  => 'failed',
				'error'   => 'Unable to reset performance test. Please try again.',
			];
		}

		/**
		 * Fires when a Rocket Insights retest job is triggered via the MCP ability.
		 *
		 * @since 3.20
		 *
		 * @param int $row_id The ID of the existing row that was queued for retesting.
		 */
		do_action( 'rocket_rocket_insights_job_retest', (int) $row->id );

		return [
			'success' => true,
			'status'  => 'triggered',
			'error'   => '',
		];
	}

	/**
	 * Handle synchronous submission of Rocket Insights retest job.
	 *
	 * This method centralizes the logic for attempting synchronous job submission
	 * and falling back to async queuing when needed. It uses JobProcessor's send_api
	 * for the actual API call, then adds Rocket Insights-specific validation and logging.
	 *
	 * Copied verbatim from AddPageInsights::handle_sync_submission() which correctly uses
	 * Logger::info() on the success path. Do NOT copy from Rest::handle_sync_submission()
	 * which has a Logger::error()-on-success bug.
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
