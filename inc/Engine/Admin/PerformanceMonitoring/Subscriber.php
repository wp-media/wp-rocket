<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\AJAX\Controller as AjaxController;

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
	 * GlobalScore instance.
	 *
	 * @var GlobalScore
	 */
	private $global_score;

	/**
	 * Constructor.
	 *
	 * @param Render         $render Render object.
	 * @param Controller     $controller Controller object.
	 * @param AjaxController $ajax_controller AjaxController object.
	 * @param GlobalScore    $global_score GlobalScore instance.
	 */
	public function __construct( Render $render, Controller $controller, AjaxController $ajax_controller, GlobalScore $global_score ) {
		$this->render          = $render;
		$this->controller      = $controller;
		$this->ajax_controller = $ajax_controller;
		$this->global_score    = $global_score;
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
			'rocket_pm_job_completed'             => 'reset_global_score',
			'rocket_pm_job_failed'                => 'reset_global_score',
			'rocket_pm_job_added'                 => 'reset_global_score',
			'rocket_pm_job_deleted'               => 'reset_global_score',
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
	 * Invalidate the global score cache.
	 *
	 * Called when any Performance Monitoring job status changes.
	 *
	 * @return void
	 */
	public function reset_global_score(): void {
		$this->global_score->reset();
	}
}
