<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Cron;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;

class Subscriber implements Subscriber_Interface {
	/**
	 * JobProcessor instance
	 *
	 * @var JobProcessor
	 */
	private $job_processor;

	/**
	 * UsedCss Query instance.
	 *
	 * @var UsedCSS_Query
	 */
	private $used_css_query;

	/**
	 * Duplicates cleanup instance.
	 *
	 * @var DuplicatesCleanup
	 */
	private $duplicates_cleanup;

	/**
	 * Instantiate the class
	 *
	 * @param JobProcessor      $job_processor JobProcessor instance.
	 * @param UsedCSS_Query     $used_css_query Usedcss Query instance.
	 * @param DuplicatesCleanup $duplicates_cleanup Duplicates cleanup instance.
	 */
	public function __construct( JobProcessor $job_processor, UsedCSS_Query $used_css_query, DuplicatesCleanup $duplicates_cleanup ) {
		$this->job_processor      = $job_processor;
		$this->used_css_query     = $used_css_query;
		$this->duplicates_cleanup = $duplicates_cleanup;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_rucss_job_check_status' => 'check_job_status',
			'init'                          => 'schedule_duplicates_cleanup',
			DuplicatesCleanup::HOOK         => 'process_duplicates_cleanup',
			'rocket_deactivation'           => 'clear_duplicates_cleanup',
		];
	}

	/**
	 * Handle old rucss job during upgrade from versions < 3.16.
	 *
	 * @param integer $row_id DB Row ID.
	 * @return void
	 */
	public function check_job_status( int $row_id ): void {
		$row = $this->used_css_query->get_row_by_id( $row_id );

		if ( ! is_object( $row ) ) {
			return;
		}

		$this->job_processor->check_job_status( $row->url, $row->is_mobile, 'rucss' );
	}

	/**
	 * Schedules the one-time removal of duplicate rows, until it is done.
	 *
	 * @return void
	 */
	public function schedule_duplicates_cleanup(): void {
		if ( $this->duplicates_cleanup->is_done() || wp_next_scheduled( DuplicatesCleanup::HOOK ) ) {
			return;
		}

		wp_schedule_single_event( time(), DuplicatesCleanup::HOOK );
	}

	/**
	 * Removes duplicate rows, and reschedules itself while the work is not finished.
	 *
	 * @return void
	 */
	public function process_duplicates_cleanup(): void {
		$result = $this->duplicates_cleanup->run();

		if ( 'partial' === $result ) {
			wp_schedule_single_event( time() + (int) rocket_get_constant( 'MINUTE_IN_SECONDS', 60 ), DuplicatesCleanup::HOOK );
		} elseif ( 'error' === $result ) {
			wp_schedule_single_event( time() + (int) rocket_get_constant( 'HOUR_IN_SECONDS', 3600 ), DuplicatesCleanup::HOOK );
		}
	}

	/**
	 * Clears the duplicates cleanup event on deactivation.
	 *
	 * @return void
	 */
	public function clear_duplicates_cleanup(): void {
		wp_clear_scheduled_hook( DuplicatesCleanup::HOOK );
	}
}
