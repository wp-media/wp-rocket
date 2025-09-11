<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\{
	Context\FreePlanContext,
	Database\Rows\PerformanceMonitoring,
	Queue\Queue,
	AJAX\Controller as AjaxController
};
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
	 * Free Plan context.
	 *
	 * @var FreePlanContext
	 */
	private $free_plan_context;

	/**
	 * Constructor.
	 *
	 * @param Render          $render Render object.
	 * @param Controller      $controller Controller object.
	 * @param AjaxController  $ajax_controller AjaxController object.
	 * @param Queue           $queue Queue object.
	 * @param FreePlanContext $free_plan_context Free Plan context.
	 */
	public function __construct( Render $render, Controller $controller, AjaxController $ajax_controller, Queue $queue, FreePlanContext $free_plan_context ) {
		$this->render            = $render;
		$this->controller        = $controller;
		$this->ajax_controller   = $ajax_controller;
		$this->queue             = $queue;
		$this->free_plan_context = $free_plan_context;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_rocket_first_install'             => 'schedule_homepage_tests',
			'rocket_dashboard_after_account_data' => [ 'render_ui', 11 ],
			'wp_ajax_rocket_pm_add_new_page'      => 'add_new_page',
			'wp_ajax_rocket_pm_get_results'       => 'get_results',
			'rocket_localize_admin_script'        => 'add_pending_ids',
			'admin_post_delete_pm'                => 'delete_row',
			'wp_ajax_rocket_pm_reset_page'        => 'reset_page',
			'init'                                => 'schedule_reset_credit',
			'rocket_pma_credit_reset'             => 'reset_credit_monthly',
			'rocket_pm_job_completed'             => 'validate_credit',
		];
	}

	/**
	 * Render the Ui in dashboard.
	 *
	 * @return void
	 */
	public function render_ui() {
		$this->render->render_ui( $this->controller->get_items() );
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
	 * Add pm_ids key to the admin ajax js variable.
	 *
	 * @param array $data Array of data.
	 * @return array
	 */
	public function add_pending_ids( array $data = [] ) {
		$data['pm_ids'] = $this->controller->get_not_finished_ids();
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
		if ( ! $this->free_plan_context->is_allowed() ) {
			$this->queue->cancel_reset_job();
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
		if ( ! $this->free_plan_context->is_allowed() ) {
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
		if ( ! $this->free_plan_context->is_allowed() ) {
			return;
		}
		$this->controller->validate_credit( $row->id );
	}
}
