<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\{
	GlobalScore,
	Jobs\Manager,
	Context\PerformanceMonitoringContext,
	Database\Queries\PerformanceMonitoring as PMQuery,
	Credit\Manager as CreditManager
};

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
	 * Credit manager instance.
	 *
	 * @var CreditManager
	 */
	private $credit_manager;

	/**
	 * GlobalScore instance.
	 *
	 * @var GlobalScore
	 */
	private $global_score;

	/**
	 * Constructor.
	 *
	 * @param PMQuery                      $query Query instance.
	 * @param Manager                      $manager Manager instance.
	 * @param PerformanceMonitoringContext $context Context instance.
	 * @param CreditManager                $credit_manager Credit manager instance.
	 * @param GlobalScore                  $global_score GlobalScore instance.
	 */
	public function __construct( PMQuery $query, Manager $manager, PerformanceMonitoringContext $context, CreditManager $credit_manager, GlobalScore $global_score ) {
		$this->query          = $query;
		$this->manager        = $manager;
		$this->context        = $context;
		$this->credit_manager = $credit_manager;
		$this->global_score   = $global_score;
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
		return $this->global_score->get_global_score_data();
	}

	/**
	 * Reset credit.
	 *
	 * @return void
	 */
	public function reset_credit() {
		$this->credit_manager->reset_credit();
	}

	/**
	 * Validate credit for DB row ID.
	 *
	 * @param int $row_id DB row ID.
	 *
	 * @return void
	 */
	public function validate_credit( $row_id ) {
		if ( $this->credit_manager->decrease_credit() ) {
			return;
		}

		$this->query->make_blurred( $row_id );
	}
}
