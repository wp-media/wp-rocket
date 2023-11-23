<?php


namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Factory;

use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Context\RetryContext;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobNotFound404;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobSetFail;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\ResetRetryProcess;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\DefaultProcess;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;


class StrategyFactory implements LoggerAwareInterface {
	use LoggerAware;

	/**
	 * UsedCSS Query
	 *
	 * @var UsedCSS_Query
	 */
	protected $used_css_query;

	/**
	 * Clock instance.
	 *
	 * @var WPRClock
	 */
	protected $clock;

	/**
	 * Constructor.
	 *
	 * @param UsedCSS_Query $used_css_query query table.
	 * @param WPRClock      $clock Clock instance.
	 */
	public function __construct( UsedCSS_Query $used_css_query, WPRClock $clock ) {
		$this->used_css_query = $used_css_query;
		$this->clock          = $clock;
	}
	/**
	 * Manage the whole process, to determine which strategy to adopt..
	 *
	 * @param object $row_details DB Row of a job.
	 * @param array  $job_details Job information from the API.
	 *
	 * @return void
	 */
	public function manage( $row_details, $job_details ): void {

		switch ( $job_details['code'] ) {
			case 408:
				$strategy = new ResetRetryProcess( $this->used_css_query );
				break;
			case 500:
			case 422:
			case 404:
			case 401:
				$strategy = new JobSetFail( $this->used_css_query );
				break;
			default:
				$strategy = new DefaultProcess( $this->used_css_query, $this->clock );
				break;
		}

		$context = new RetryContext();
		$context->set_strategy( $strategy );
		$context->execute( $row_details, $job_details );
	}
}
