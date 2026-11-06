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
	 * Instantiate the class
	 *
	 * @param JobProcessor  $job_processor JobProcessor instance.
	 * @param UsedCSS_Query $used_css_query Usedcss Query instance.
	 */
	public function __construct( JobProcessor $job_processor, UsedCSS_Query $used_css_query ) {
		$this->job_processor  = $job_processor;
		$this->used_css_query = $used_css_query;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_rucss_job_check_status' => 'check_job_status',
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
}
