<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights;

use WP_Rocket\Engine\Admin\RocketInsights\{
	Context\Context,
	Database\Rows\RocketInsights as RIRow,
	Managers\Plan,
	Jobs\Manager,
	Queue\Queue,
	AJAX\Controller as AjaxController
};
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

/**
 * Rocket Insights Subscriber
 *
 * Handles events and hooks for Rocket Insights functionality
 */
class Subscriber implements Subscriber_Interface, LoggerAwareInterface {
	use LoggerAware;

	/**
	 * Render object.
	 *
	 * @var Render
	 */
	private $render;

	/**
	 * Controller object.
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * AjaxController object.
	 *
	 * @var AjaxController
	 */
	private $ajax_controller;

	/**
	 * Queue object.
	 *
	 * @var Queue
	 */
	private $queue;

	/**
	 * Rocket Insights context.
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
	 * Plugin options.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Manager instance.
	 *
	 * @var Manager
	 */
	private $manager;

	/**
	 * Plan manager instance.
	 *
	 * @var Plan
	 */
	private $plan;

	/**
	 * Constructor.
	 *
	 * @param Render         $render Render object.
	 * @param Controller     $controller Controller object.
	 * @param AjaxController $ajax_controller AjaxController object.
	 * @param Queue          $queue Queue object.
	 * @param Context        $context Rocket Insights context.
	 * @param GlobalScore    $global_score GlobalScore instance.
	 * @param Options_Data   $options Options instance.
	 * @param Manager        $manager Manager instance.
	 * @param Plan           $plan Plan manager.
	 */
	public function __construct(
		Render $render,
		Controller $controller,
		AjaxController $ajax_controller,
		Queue $queue,
		Context $context,
		GlobalScore $global_score,
		Options_Data $options,
		Manager $manager,
		Plan $plan
	) {
		$this->render          = $render;
		$this->controller      = $controller;
		$this->ajax_controller = $ajax_controller;
		$this->queue           = $queue;
		$this->context         = $context;
		$this->global_score    = $global_score;
		$this->options         = $options;
		$this->manager         = $manager;
		$this->plan            = $plan;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_rocket_first_install'                     => [
				[ 'reset_credit_monthly', 9 ],
				[ 'schedule_homepage_tests' ],
			],
			'wp_ajax_rocket_rocket_insights_add_new_page' => 'add_new_page',
			'wp_ajax_rocket_rocket_insights_get_results'  => 'get_results',
			'admin_post_rocket_rocket_insights_delete'    => 'delete_row',
			'wp_ajax_rocket_rocket_insights_reset_page'   => 'reset_page',
			'rocket_localize_admin_script'                => 'add_pending_ids',
			'rocket_rocket_insights_credit_reset'         => 'reset_credit_monthly',
			'rocket_rocket_insights_job_completed'        => [
				[ 'validate_credit' ],
				[ 'reset_global_score' ],
			],
			'rocket_rocket_insights_job_failed'           => 'reset_global_score',
			'rocket_rocket_insights_job_added'            => 'reset_global_score',
			'rocket_rocket_insights_job_retest'           => 'reset_global_score',
			'rocket_rocket_insights_job_deleted'          => 'reset_global_score',
			'rocket_dashboard_sidebar'                    => 'render_global_score_widget',
			'rocket_insights_tab_content'                 => [
				[ 'render_license_banner_section', 10 ],
				[ 'maybe_show_paid_reach_limits_notice', 17 ],
				[ 'maybe_show_notice', 18 ],
				[ 'render_performance_urls_table', 20 ],
			],
			'admin_init'                                  => [
				[ 'flush_license_cache', 8 ],
				[ 'check_upgrade' ],
				[ 'schedule_reset_credit' ],
			],
			'admin_post_rocket_rocket_insights_add_homepage' => 'add_homepage_from_widget',
			'rocket_deactivation'                         => [
				[ 'cancel_scheduled_jobs' ],
				[ 'remove_current_plan' ],
			],
			'init'                                        => [
				[ 'maybe_cancel_scheduled_jobs' ],
				[ 'maybe_schedule_next_test' ],
			],
			'cron_schedules'                              => 'maybe_add_monthly_schedule',
			'rocket_options_changed'                      => 'maybe_cancel_scheduled_jobs',
			'rocket_rocket_insights_retest_all_pages'     => 'retest_all_pages',
		];
	}

	/**
	 * Schedules homepage performance tests on plugin activation.
	 *
	 * This method is triggered when the plugin is first installed.
	 * It schedules both desktop and mobile tests for the homepage URL.
	 *
	 * @return void
	 */
	public function schedule_homepage_tests(): void {
		$this->controller->add_homepage();
	}

	/**
	 * Handles the AJAX request to add a new page for performance monitoring.
	 *
	 * @return void
	 */
	public function add_new_page(): void {
		$this->ajax_controller->add_new_page();
	}

	/**
	 * Handles the AJAX request to get results of urls for performance monitoring.
	 *
	 * @return void
	 */
	public function get_results(): void {
		$this->ajax_controller->get_results();
	}

	/**
	 * Add rocket_insights_ids key to the admin ajax js variable.
	 *
	 * @param array $data Array of data.
	 * @return array
	 */
	public function add_pending_ids( array $data = [] ) {
		if ( ! $this->context->is_allowed() ) {
			return $data;
		}

		$data['rocket_insights_ids']               = $this->controller->get_not_finished_ids();
		$data['rocket_insights_no_credit_tooltip'] = __( 'Upgrade your plan to get access to re-test performance or run new tests', 'rocket' );
		$data['is_free']                           = (int) $this->context->is_free_user();

		$global_score_data                   = $this->controller->get_global_score();
		$global_score_data['status_color']   = $this->render->get_score_color_status( (int) $global_score_data['score'] );
		$global_score_data['remaining_urls'] = $this->controller->get_remaining_url_count();

		$data['global_score_data'] = [
			'data'     => $global_score_data,
			'html'     => $this->render->get_global_score_widget_content( $global_score_data ),
			'row_html' => $this->render->get_global_score_row( $global_score_data ),
		];

		return $data;
	}

	/**
	 * Delete one row.
	 *
	 * @return void
	 */
	public function delete_row() {
		$this->controller->delete_row();
	}

	/**
	 * Reset testing a page/url.
	 *
	 * @return void
	 */
	public function reset_page(): void {
		$this->ajax_controller->reset_page();
	}

	/**
	 * Schedule reset credit recurring AS task.
	 *
	 * @return void
	 */
	public function schedule_reset_credit(): void {
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		if ( ! $this->context->is_free_user() ) {
			$this->cancel_scheduled_jobs();
			return;
		}

		$this->queue->schedule_reset_task();
	}

	/**
	 * Callback to reset the credit for the recurring task hook.
	 *
	 * @return void
	 */
	public function reset_credit_monthly() {
		if ( ! $this->context->is_allowed() || ! $this->context->is_free_user() ) {
			return;
		}
		$this->controller->reset_credit();
	}

	/**
	 * Validate credit with job success.
	 *
	 * @param RIRow $row DB row.
	 *
	 * @return void
	 */
	public function validate_credit( $row ) {
		if ( ! $this->context->is_allowed() || ! $this->context->is_free_user() ) {
			return;
		}
		$this->controller->validate_credit( $row->id );
	}

	/**
	 * Invalidate the global score cache.
	 *
	 * Called when any Performance Monitoring job status changes.
	 *
	 * @return void
	 */
	public function reset_global_score(): void {
		if ( ! $this->context->is_allowed() ) {
			return;
		}
		$this->global_score->reset();
	}

	/**
	 * Render the global performance score widget in the dashboard sidebar.
	 *
	 * @return void
	 */
	public function render_global_score_widget(): void {
		if ( ! $this->context->is_allowed() ) {
			return;
		}
		$data                   = $this->controller->get_global_score();
		$data['remaining_urls'] = $this->controller->get_remaining_url_count();
		$this->render->render_global_score_widget( $data );
	}

	/**
	 * Adds homepage for monitoring from the dashboard widget.
	 *
	 * @return void
	 */
	public function add_homepage_from_widget(): void {
		$this->controller->add_homepage_from_widget();
	}

	/**
	 * Render performance URLs table in the Rocket Insights tab.
	 *
	 * @return void
	 */
	public function render_performance_urls_table() {
		// Hide Rocket Insights content for reseller accounts and non-live installations.
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$license_data = $this->controller->get_license_data();

		$this->render->render_rocket_insights_urls_table(
			[
				'items'                       => $this->controller->get_items(),
				'global_score'                => $this->controller->get_global_score(),
				'remaining_urls'              => $this->controller->get_remaining_url_count(),
				'rocket_insights_addon_limit' => $this->controller->get_rocket_insights_addon_limit(),
				'upgrade_url'                 => $license_data['btn_url'] ?? '',
				'can_add_pages'               => $this->context->is_adding_page_allowed(),
				'show_quota_banner'           => $this->should_show_quota_banner(),
				'is_free'                     => $this->context->is_free_user(),
			]
		);
	}

	/**
	 * Determine if the quota banner should be displayed.
	 *
	 * Shows banner when free users have reached URL limit OR exhausted credits.
	 *
	 * @return bool True if the quota banner should be shown.
	 */
	private function should_show_quota_banner(): bool {
		if ( ! $this->context->is_free_user() ) {
			return false;
		}

		$remaining_url_count = $this->controller->get_remaining_url_count();

		// Show banner if URL limit reached OR no credits left.
		return empty( $remaining_url_count ) || ! $this->controller->has_credit();
	}

	/**
	 * Render the license banner section in the Performance Monitoring tab.
	 *
	 * @return void
	 */
	public function render_license_banner_section() {
		// Hide Rocket Insights content for reseller accounts and non-live installations.
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		if ( ! $this->controller->display_banner() ) {
			return;
		}
		// add some logic here to check if the banner should be displayed.
		$this->render->render_license_banner_section( $this->controller->get_license_data() );
	}

	/**
	 * Check if the plugin was upgraded.
	 *
	 * @return void
	 */
	public function flush_license_cache() {
		if ( ! isset( $_GET['rocket_insights_upgrade'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$this->plan->remove_customer_data_cache();
		rocket_renew_box( 'insights_upgrade' );

		wp_safe_redirect( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '#rocket_insights' ) );
	}

	/**
	 * Cancel scheduled jobs with plugin deactivation.
	 *
	 * @return void
	 */
	public function cancel_scheduled_jobs() {
		$this->queue->cancel_reset_job();
	}

	/**
	 * Check plan upgrade.
	 *
	 * @return void
	 */
	public function check_upgrade() {
		$this->plan->check_upgrade();
	}

	/**
	 * Remove current plan with plugin deactivation.
	 *
	 * @return void
	 */
	public function remove_current_plan() {
		$this->plan->remove_current_plan();
	}

	/**
	 * Maybe show upgrade notice.
	 *
	 * @return void
	 */
	public function maybe_show_notice() {
		$this->controller->maybe_show_notice();
	}

	/**
	 * Maybe show notice for paid users when reaching limits.
	 *
	 * @return void
	 */
	public function maybe_show_paid_reach_limits_notice() {
		$this->controller->maybe_show_paid_reach_limits_notice();
	}

	/**
	 * Schedule the next test for performance monitoring under certain conditions.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function maybe_schedule_next_test() {
		if ( ! $this->should_schedule_next_test() ) {
			return;
		}

		$this->schedule_retest_event();
	}

	/**
	 * Determines if the next test should be scheduled based on user plan and settings.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the next test should be scheduled.
	 */
	private function should_schedule_next_test(): bool {
		// Only premium users can schedule tests.
		if ( $this->context->is_free_user() ) {
			return false;
		}

		// Performance monitoring must be enabled.
		if ( ! $this->options->get( 'performance_monitoring' ) ) {
			return false;
		}

		// Don't schedule if already scheduled.
		return ! $this->is_retest_event_scheduled();
	}

	/**
	 * Schedules the daily retest event.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function schedule_retest_event(): void {

		$schedule = $this->options->get( 'performance_monitoring_schedule_frequency', 'monthly' );

		wp_schedule_event( time(), $schedule, 'rocket_rocket_insights_retest_all_pages' );
	}

	/**
	 * Add monthly schedule to cron schedules.
	 *
	 * @param array|mixed $schedules Cron schedules.
	 *
	 * @return array|mixed
	 */
	public function maybe_add_monthly_schedule( $schedules ) {
		if ( ! is_array( $schedules ) ) {
			return $schedules;
		}

		if ( isset( $schedules['monthly'] ) ) {
			return $schedules;
		}

		$schedules['monthly'] = [
			'interval' => MONTH_IN_SECONDS,
			'display'  => __( 'Once a month', 'rocket' ),
		];

		return $schedules;
	}

	/**
	 * Retest all pages.
	 *
	 * @return void
	 */
	public function retest_all_pages() {
		foreach ( $this->controller->get_items() as $item ) {
			$this->manager->add_to_the_queue(
				$item->url,
				$item->is_mobile,
				[
					'data'       => [
						'is_retest' => true,
					],
					'score'      => '',
					'report_url' => '',
					'is_blurred' => 0,
				]
				);
		}
	}

	/**
	 * Cancels scheduled jobs for performance monitoring if the user is on the free plan
	 * and performance monitoring is disabled.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function maybe_cancel_scheduled_jobs() {
		if ( ! $this->should_cancel_scheduled_jobs() ) {
			return;
		}

		$this->cancel_retest_scheduled_event();
	}

	/**
	 * Determines if scheduled jobs should be cancelled based on user plan and settings.
	 *
	 * @since TBD
	 *
	 * @return bool Whether scheduled jobs should be cancelled.
	 */
	private function should_cancel_scheduled_jobs(): bool {
		// Only free users might need cancellation.
		if ( ! $this->context->is_free_user() ) {
			return false;
		}

		// If performance monitoring is enabled, don't cancel.
		if ( $this->options->get( 'performance_monitoring' ) ) {
			return false;
		}

		// Only cancel if there's an event scheduled.
		return $this->is_retest_event_scheduled();
	}

	/**
	 * Checks if the retest event is scheduled.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the retest event is scheduled.
	 */
	private function is_retest_event_scheduled(): bool {
		return (bool) wp_next_scheduled( 'rocket_rocket_insights_retest_all_pages' );
	}

	/**
	 * Cancels the scheduled retest event.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function cancel_retest_scheduled_event(): void {
		$next_event = wp_next_scheduled( 'rocket_rocket_insights_retest_all_pages' );

		if ( ! $next_event ) {
			return;
		}

		wp_unschedule_event( $next_event, 'rocket_rocket_insights_retest_all_pages' );
	}
}
