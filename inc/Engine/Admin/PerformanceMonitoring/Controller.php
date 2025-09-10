<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PMQuery;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs\Manager;

class Controller {
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
	 * @var PerformanceMonitoringContext
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param PMQuery                      $query Query instance.
	 * @param Manager                      $manager Manager instance.
	 * @param PerformanceMonitoringContext $context Context instance.
	 */
	public function __construct( PMQuery $query, Manager $manager, PerformanceMonitoringContext $context ) {
		$this->query   = $query;
		$this->manager = $manager;
		$this->context = $context;
	}

	/**
	 * Get items from the database.
	 *
	 * @return array|int
	 */
	public function get_items() {
		$query_params = [
			'orderby' => 'modified',
			'order'   => 'asc',
			'number'  => 20,
		];
		return $this->query->query( $query_params );
	}

	/**
	 * Add homepage to the database to be queued.
	 *
	 * @return void
	 */
	public function add_homepage() {
		$this->manager->add_url_to_the_queue( home_url(), true );

		/**
		 * Fires when a performance monitoring job is added.
		 *
		 * @since 3.20
		 *
		 * @param string $url The URL that was added for monitoring.
		 */
		do_action( 'rocket_pm_job_added', home_url() );
	}

	/**
	 * Get not finished IDs.
	 *
	 * @return array
	 */
	public function get_not_finished_ids() {
		return $this->query->get_not_finished_ids();
	}

	/**
	 * Delete one row.
	 *
	 * @return void
	 */
	public function delete_row() {
		if ( ! $this->context->is_allowed() ) {
			wp_die();
		}

		if (
			! isset( $_GET['_wpnonce'] )
			||
			! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'delete_pm' )
		) {
			wp_nonce_ays( 'delete_pm' );
		}

		$id = ! empty( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		if ( ! empty( $id ) ) {
			$this->query->delete_item( $id );

			/**
			 * Fires when a performance monitoring job is deleted.
			 *
			 * @since 3.20
			 *
			 * @param int $id The ID of the deleted performance monitoring job.
			 */
			do_action( 'rocket_pm_job_deleted', $id );
		}

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
	}

	/**
	 * Get global score data.
	 *
	 * @return array
	 */
	public function get_global_score() {
		return [
			'status'    => 'no-url', // Values are no-url, in-progress, complete, blurred.
			'pages_num' => 1,
			'score'     => 85, // Fake in case of blurred.
		];
	}
}
