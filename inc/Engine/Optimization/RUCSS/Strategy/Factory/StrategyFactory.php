<?php


namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Factory;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Context\RetryContext;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobFoundNoResult;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobNotFound;
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
	 * Constructor.
	 *
	 * @param UsedCSS_Query $used_css_query query table.
	 */
	public function __construct(UsedCSS_Query $used_css_query) {
		$this->used_css_query = $used_css_query;
	}
	/**
	 * Manage the whole process, to determine which strategy to adopt..
	 *
	 * @param object $row_details DB Row of a job.
	 * @param array  $job_details Job information from the API.
	 *
	 * @return void
	 */
	public function manage( $row_details, $job_details): void {
		$strategy = new DefaultProcess( $this->used_css_query );
//		$this->logger::debug( 'RUCSS: Job status failed for url: ' . $row_details->url, $job_details );

		switch ( $job_details['code'] ) {
			case 404:
//				$this->logger::debug('Strategy Factory -> Error 404 -> JobNotFound');
				$strategy = new JobNotFound( $this->used_css_query );
				break;
			case 400:
//				$this->logger::debug('Strategy Factory -> Error 400 -> JobFoundNoResult');
				$strategy = new JobFoundNoResult( $this->used_css_query );
				break;
			case 408:
//				$this->logger::debug('Strategy Factory -> Error 408 -> ResetRetryProcess');
				$strategy = new ResetRetryProcess( $this->used_css_query );
				break;
			default:
//				$this->logger::debug('Strategy Factory -> Other -> DefaultProcess');
				break;
		}

		$context = new RetryContext();
		$context->set_strategy( $strategy );
		$context->execute( $row_details, $job_details );

		// Increment the retries number with 1 , Change status to pending again and change job id on timeout.
		$this->used_css_query->increment_retries( $row_details->id, (int) $row_details->retries );
//		$this->logger::debug( 'RUCSS: Job failed ' . $row_details->retries . ' times for url: ' . $row_details->url );

		$this->used_css_query->update_message( $row_details->id, $job_details['code'], $job_details['message'], $row_details->error_message );

		return;
	}
}
