<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights;

use WP_Rocket\Engine\Admin\RocketInsights\{
	Context\Context,
	Database\Rows\RocketInsights as RIRow,
	Managers\Plan,
	Jobs\Manager,
	Queue\Queue,
};
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\Renewal;
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
	 * Rest object.
	 *
	 * @var Rest
	 */
	private $rest;

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
	 * Renewal instance.
	 *
	 * @var Renewal
	 */
	private $renewal;

	/**
	 * Constructor.
	 *
	 * @param Render       $render Render object.
	 * @param Controller   $controller Controller object.
	 * @param Rest         $rest Rest object.
	 * @param Queue        $queue Queue object.
	 * @param Context      $context Rocket Insights context.
	 * @param GlobalScore  $global_score GlobalScore instance.
	 * @param Options_Data $options Options instance.
	 * @param Manager      $manager Manager instance.
	 * @param Plan         $plan Plan manager.
	 * @param Renewal      $renewal Renewal instance.
	 */
	public function __construct(
		Render $render,
		Controller $controller,
		Rest $rest,
		Queue $queue,
		Context $context,
		GlobalScore $global_score,
		Options_Data $options,
		Manager $manager,
		Plan $plan,
		Renewal $renewal
	) {
		$this->render       = $render;
		$this->controller   = $controller;
		$this->rest         = $rest;
		$this->queue        = $queue;
		$this->context      = $context;
		$this->global_score = $global_score;
		$this->options      = $options;
		$this->manager      = $manager;
		$this->plan         = $plan;
		$this->renewal      = $renewal;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_rocket_first_install'               => [
				[ 'reset_credit_monthly', 9 ],
				[ 'schedule_homepage_tests' ],
			],
			'admin_post_delete_rocket_insights_url' => 'delete_row',
			'rocket_localize_admin_script'          => 'add_pending_ids',
			'rocket_insights_credit_reset'          => 'reset_credit_monthly',
			'rocket_insights_auto_add_homepage'     => 'maybe_add_homepage_automatically',
			'rocket_rocket_insights_job_completed'  => [
				[ 'validate_credit' ],
				[ 'reset_global_score' ],
			],
			'rocket_rocket_insights_job_failed'     => 'reset_global_score',
			'rocket_rocket_insights_job_added'      => 'reset_global_score',
			'rocket_rocket_insights_job_retest'     => 'reset_global_score',
			'rocket_rocket_insights_job_deleted'    => 'reset_global_score',
			'rocket_dashboard_sidebar'              => 'render_global_score_widget',
			'rocket_insights_tab_content'           => [
				[ 'render_license_banner_section', 10 ],
				[ 'maybe_show_paid_reach_limits_notice', 17 ],
				[ 'maybe_show_notice', 18 ],
				[ 'render_performance_urls_table', 20 ],
			],
			'admin_init'                            => [
				[ 'flush_license_cache', 8 ],
				[ 'check_upgrade' ],
				[ 'schedule_jobs', 11 ],
			],
			'admin_post_rocket_rocket_insights_add_homepage' => 'add_homepage_from_widget',
			'rocket_deactivation'                   => [
				[ 'cancel_scheduled_jobs' ],
				[ 'remove_current_plan' ],
			],
			'rocket_options_changed'                => 'maybe_cancel_automatic_retest_job',
			'rocket_insights_retest'                => 'retest_all_pages',
			'wp_rocket_upgrade'                     => [
				[ 'on_update_reset_credit', 10, 2 ],
				[ 'on_update_cancel_old_as_jobs', 10, 2 ],
			],
			'admin_notices'                         => 'maybe_display_rocket_insights_promotion_notice',
			'rocket_rocket_insights_enabled'        => 'maybe_disable_for_reseller_or_non_live',
			'rest_api_init'                         => [ 'register_routes' ],
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
	 * Schedule recurring AS jobs.
	 *
	 * @return void
	 */
	public function schedule_jobs(): void {
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$this->schedule_auto_add_homepage_task();

		if ( ! $this->context->is_free_user() ) {
			$this->queue->cancel_credit_reset_job();
			$this->schedule_retest_task();

			return;
		}

		$this->queue->schedule_credit_reset_task();
		$this->cancel_retest_job();
	}

	/**
	 * Schedule retest task.
	 *
	 * @return void
	 */
	private function schedule_retest_task() {
		if ( ! $this->context->is_schedule_allowed() ) {
			$this->cancel_retest_job();
			return;
		}

		$schedule_frequency = $this->options->get( 'performance_monitoring_schedule_frequency', MONTH_IN_SECONDS );
		$this->queue->schedule_retest_task( $schedule_frequency );
	}

	/**
	 * Cancel retest job.
	 *
	 * @return void
	 */
	private function cancel_retest_job() {
		$this->queue->cancel_retest_job();
	}

	/**
	 * Get the expiry interval for auto-add homepage feature.
	 *
	 * @since 3.20.3
	 *
	 * @return int Number of days before expiry, or 0 to disable.
	 */
	private function get_expiry_interval(): int {
		/**
		 * Filters the number of days before license expiry to automatically add homepage.
		 *
		 * @since 3.20.3
		 *
		 * @param int $interval Number of days before expiry. Set to 0 to disable auto-add.
		 * @return int
		 */
		return (int) wpm_apply_filters_typed(
			'integer',
			'rocket_insights_add_homepage_expiry_interval',
			1
		);
	}

	/**
	 * Schedule auto-add homepage task.
	 *
	 * Schedules the task only when:
	 * - No URLs are tracked
	 * - Feature is not disabled (interval > 0)
	 *
	 * @since 3.20.3
	 *
	 * @return void
	 */
	private function schedule_auto_add_homepage_task(): void {
		$interval = $this->get_expiry_interval();

		// Don't schedule if feature is disabled.
		if ( empty( $interval ) ) {
			$this->queue->cancel_auto_add_homepage_task();
			return;
		}

		// Don't schedule if URLs already exist.
		if ( 0 < $this->controller->get_total_url_count() ) {
			$this->queue->cancel_auto_add_homepage_task();
			return;
		}

		// Schedule the task.
		$this->queue->schedule_auto_add_homepage_task();
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

		wp_safe_redirect( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '&rocket_source=purchase_redirect#rocket_insights' ) );
	}

	/**
	 * Cancel scheduled jobs with plugin deactivation.
	 *
	 * @return void
	 */
	public function cancel_scheduled_jobs() {
		$this->queue->cancel_all_tasks();
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
	 * Maybe add homepage automatically when license is expiring and no URLs are tracked.
	 *
	 * This method is called daily by the scheduled cron task. It checks if:
	 * - Rocket Insights is enabled
	 * - The feature is not disabled (interval > 0)
	 * - No URLs are currently tracked
	 * - License is expiring within the configured interval
	 *
	 * If all conditions are met, it adds the homepage and cancels the recurring task.
	 *
	 * @since 3.20.3
	 *
	 * @return void
	 */
	public function maybe_add_homepage_automatically(): void {
		// Guard: Rocket Insights disabled.
		if ( ! $this->context->is_allowed() ) {
			$this->queue->cancel_auto_add_homepage_task();
			return;
		}

		$interval = $this->get_expiry_interval();

		// Guard: Feature disabled.
		if ( 0 === $interval ) {
			$this->queue->cancel_auto_add_homepage_task();
			return;
		}

		// Guard: Already have URLs - cancel task.
		if ( $this->controller->get_total_url_count() > 0 ) {
			$this->queue->cancel_auto_add_homepage_task();
			return;
		}

		// Guard: Not expiring soon.
		if ( ! $this->renewal->is_expiring_in( $interval ) ) {
			return;
		}

		// Add homepage and cancel future runs.
		$this->controller->add_homepage( 'cron_update' );
		$this->queue->cancel_auto_add_homepage_task();
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
						'source'    => 'performance monitoring',
					],
					'score'      => '',
					'report_url' => '',
					'is_blurred' => 0,
				]
			);
		}
		$this->reset_global_score();
	}

	/**
	 * Cancels scheduled jobs for performance monitoring if the user is on the free plan
	 * and performance monitoring is disabled.
	 *
	 * @return void
	 */
	public function maybe_cancel_automatic_retest_job() {
		$this->queue->cancel_retest_job();
	}

	/**
	 * Callback for the wp_rocket_upgrade action to reset credit on version update.
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 * @return void
	 */
	public function on_update_reset_credit( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.20.0', '<' ) ) {
			$this->controller->reset_credit();
		}
	}

	/**
	 * Register REST API routes for Rocket Insights.
	 *
	 * @return void
	 */
	public function register_routes() {
		$this->rest->register_routes();
	}

	/**
	 * Callback for the wp_rocket_upgrade action to cancel deprecated Action Scheduler jobs on version update.
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 * @return void
	 */
	public function on_update_cancel_old_as_jobs( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.20.1', '>=' ) ) {
			return;
		}
		$this->queue->deprecate_old_actions();
	}

	/**
	 * Displays a promotion notice for Rocket Insights on the admin dashboard.
	 *
	 * @since 3.20.1
	 *
	 * @return void
	 */
	public function maybe_display_rocket_insights_promotion_notice() {
		// Hide Rocket Insights notice if the feature is disabled.
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		if ( 0 < $this->controller->get_total_url_count() ) {
			return;
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$notice_name = 'rocket_insights_promotion_notice';

		if ( in_array( $notice_name, (array) get_user_meta( get_current_user_id(), 'rocket_boxes', true ), true ) ) {
			return;
		}

		$message = sprintf(
		// translators: %1$s opening tag, %2$s is WP Rocket plugin name, %3$s closing tag.
			esc_html__(
				'%1$sNew in %2$s: Meet Rocket Insights, your built-in performance tracking tool!%3$s',
				'rocket'
			),
			'<p><strong>',
			rocket_get_constant( 'WP_ROCKET_PLUGIN_NAME' ),
			'</strong></p>'
		);

		$message .= sprintf(
		// translators: %1$s opening tag, %2$s is WP Rocket plugin name, %3$s closing tag.
			esc_html__(
				'%1$sStarting from %2$s 3.20, you can track your key pages’ performance directly from your dashboard and get in-depth insights.%3$s',
				'rocket'
			),
			'<p>',
			rocket_get_constant( 'WP_ROCKET_PLUGIN_NAME' ),
			'</p>'
		);

		$message .= sprintf(
		// translators: %1$s opening tag, %3$s closing tag.
			esc_html__(
				'%1$sAdd your first page, run the test, and keep your site fast.%2$s',
				'rocket'
			),
			'<p>🚀',
			'</p>'
		);

		rocket_notice_html(
			[
				'status'               => 'success',
				'message'              => $message,
				'action'               => 'rocket_insights_page',
				'dismiss_button'       => $notice_name,
				'dismiss_button_class' => 'button button-primary',
			]
		);
	}

	/**
	 * Filters the rocket_rocket_insights_enabled value to disable for resellers and non-live sites.
	 *
	 * @since 3.20.2
	 *
	 * @param bool $enabled Whether Rocket Insights is enabled.
	 * @return bool
	 */
	public function maybe_disable_for_reseller_or_non_live( bool $enabled ): bool {
		if ( ! $enabled ) {
			return $enabled;
		}

		return ! $this->context->is_reseller_or_non_live();
	}
}
