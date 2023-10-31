<?php


namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Factory;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Context\RetryContext;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobFoundNoResult;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobNotFound;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\ResetRetryProcess;

class StrategyFactory {

	/**
	 * UsedCSS Query
	 *
	 * @var UsedCSS_Query
	 */
	protected $used_css_query;

	/**
	 * Manage the whole process, to determine which strategy to adopt..
	 *
	 * @param object $row_details DB Row of a job.
	 * @param array  $job_details Job information from the API.
	 *
	 * @return void
	 */
	public function manage( $row_details, $job_details ): void {
		$strategy = null;
		switch ( $job_details['code'] ) {
			case 404:
				$strategy = new JobNotFound( $this->used_css_query );
				break;
			case 400:
				$strategy = new JobFoundNoResult( $this->used_css_query );
				break;
			case 408:
				$strategy = new ResetRetryProcess( $this->used_css_query );
				break;
		}

		if ( null !== $strategy ) {
			$context = new RetryContext();
			$context->set_strategy( $strategy );
			$context->execute( $row_details, $job_details );
		}

		return;
	}
}
