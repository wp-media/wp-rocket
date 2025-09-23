<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\{Context\PerformanceMonitoringContext,
	Database\Rows\PerformanceMonitoring,
	Managers\Plan,
	Queue\Queue,
	AJAX\Controller as AjaxController};
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

/**
 * Performance Monitoring Subscriber
 *
 * Handles events and hooks for Performance Monitoring functionality
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
	 * PMA context.
	 *
	 * @var PerformanceMonitoringContext
	 */
	private $pma_context;

	/**
	 * GlobalScore instance.
	 *
	 * @var GlobalScore
	 */
	private $global_score;

	/**
	 * Plan manager instance.
	 *
	 * @var Plan
	 */
	private $plan;

	/**
	 * Constructor.
	 *
	 * @param Render                       $render Render object.
	 * @param Controller                   $controller Controller object.
	 * @param AjaxController               $ajax_controller AjaxController object.
	 * @param Queue                        $queue Queue object.
	 * @param PerformanceMonitoringContext $pma_context PMA context.
	 * @param GlobalScore                  $global_score GlobalScore instance.
	 * @param Plan                         $plan Plan manager.
	 */
	public function __construct(
		Render $render,
		Controller $controller,
		AjaxController $ajax_controller,
		Queue $queue,
		PerformanceMonitoringContext $pma_context,
		GlobalScore $global_score,
		Plan $plan
	) {
		$this->render          = $render;
		$this->controller      = $controller;
		$this->ajax_controller = $ajax_controller;
		$this->queue           = $queue;
		$this->pma_context     = $pma_context;
		$this->global_score    = $global_score;
		$this->plan            = $plan;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_rocket_first_install'           => 'schedule_homepage_tests',
			'wp_ajax_rocket_pm_add_new_page'    => 'add_new_page',
			'wp_ajax_rocket_pm_get_results'     => 'get_results',
			'admin_post_delete_pm'              => 'delete_row',
			'wp_ajax_rocket_pm_reset_page'      => 'reset_page',
			'rocket_localize_admin_script'      => 'add_pending_ids',
			'rocket_pma_credit_reset'           => 'reset_credit_monthly',
			'rocket_pm_job_completed'           => [
				[ 'validate_credit' ],
				[ 'reset_global_score' ],
			],
			'rocket_pm_job_failed'              => 'reset_global_score',
			'rocket_pm_job_added'               => 'reset_global_score',
			'rocket_pm_job_retest'              => 'reset_global_score',
			'rocket_pm_job_deleted'             => 'reset_global_score',
			'rocket_dashboard_sidebar'          => 'render_global_score_widget',
			'rocket_insights_tab_content'       => [
				[ 'render_license_banner_section', 10 ],
				[ 'maybe_show_notice', 18 ],
				[ 'render_performance_urls_table', 20 ],
				[ 'render_settings_section', 30 ],
			],
			'admin_init'                        => [
				[ 'flush_license_cache', 8 ],
				[ 'check_upgrade' ],
				[ 'schedule_reset_credit' ],
			],
			'admin_post_rocket_pm_add_homepage' => 'add_homepage_from_widget',
			'rocket_deactivation'               => [
				[ 'cancel_scheduled_jobs' ],
				[ 'remove_current_plan' ],
			],
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
	 * Add pm_ids, remaining_urls & pm_max_urls_reached_message key to the admin ajax js variable.
	 *
	 * @param array $data Array of data.
	 * @return array
	 */
	public function add_pending_ids( array $data = [] ) {
		if ( ! $this->pma_context->is_allowed() ) {
			return $data;
		}

		$data['pm_ids']                      = $this->controller->get_not_finished_ids();
		$data['remaining_urls']              = $this->controller->get_remaining_url_count();
		$data['pm_max_urls_reached_message'] = __( 'Maximum number of URLs reached for your license.', 'rocket' );
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
		if ( ! $this->pma_context->is_allowed() ) {
			return;
		}

		if ( ! $this->pma_context->is_free_user() ) {
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
		if ( ! $this->pma_context->is_allowed() || ! $this->pma_context->is_free_user() ) {
			return;
		}
		$this->controller->reset_credit();
	}

	/**
	 * Validate credit with job success.
	 *
	 * @param PerformanceMonitoring $row DB row.
	 *
	 * @return void
	 */
	public function validate_credit( $row ) {
		if ( ! $this->pma_context->is_allowed() || ! $this->pma_context->is_free_user() ) {
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
		if ( ! $this->pma_context->is_allowed() ) {
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
	 * Render the performance URLs table in the Performance Monitoring tab.
	 *
	 * @return void
	 */
	public function render_performance_urls_table() {
		$license_data = $this->controller->get_license_data();
		$this->render->render_pma_urls_table(
			[
				'items'           => $this->controller->get_items(),
				'global_score'    => $this->controller->get_global_score(),
				'remaining_urls'  => $this->controller->get_remaining_url_count(),
				'pma_addon_limit' => $this->controller->get_pma_addon_limit(),
				'upgrade_url'     => $license_data['btn_url'] ?? '',
				'can_add_pages'   => wpm_apply_filters_typesafe( 'wpr_pm_allow_add_page', true ),
			]
		);
	}

	/**
	 * Render the settings section in the Performance Monitoring tab.
	 *
	 * @return void
	 */
	public function render_settings_section() {
		$this->render->render_settings_section( $this->controller->get_settings_section_data() );
	}

	/**
	 * Render the license banner section in the Performance Monitoring tab.
	 *
	 * @return void
	 */
	public function render_license_banner_section() {
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
		if ( ! isset( $_GET['rocket_pma_upgrade'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
}
