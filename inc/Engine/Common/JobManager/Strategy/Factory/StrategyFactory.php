<?php


namespace WP_Rocket\Engine\Common\JobManager\Strategy\Factory;

use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Common\JobManager\Strategy\Context\RetryContext;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\JobSetFail;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\ResetRetryProcess;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\DefaultProcess;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;


class StrategyFactory implements LoggerAwareInterface {
	use LoggerAware;

	/**
	 * Clock instance.
	 *
	 * @var WPRClock
	 */
	protected $clock;

	/**
	 * Constructor.
	 *
	 * @param WPRClock $clock Clock instance.
	 */
	public function __construct( WPRClock $clock ) {
		$this->clock = $clock;
	}
	/**
	 * Manage the whole process, to determine which strategy to adopt..
	 *
	 * @param object           $row_details DB Row of a job.
	 * @param array            $job_details Job information from the API.
	 * @param ManagerInterface $manager Job Manager.
	 *
	 * @return void
	 */
	public function manage( $row_details, $job_details, ManagerInterface $manager ): void {

		switch ( $job_details['code'] ) {
			case 408:
				$strategy = new ResetRetryProcess( $manager );
				break;
			case 500:
			case 422:
			case 404:
			case 401:
				$strategy = new JobSetFail( $manager );
				break;
			default:
				$strategy = new DefaultProcess( $manager, $this->clock );
				break;
		}

		$context = new RetryContext();
		$context->set_strategy( $strategy );
		$context->execute( $row_details, $job_details );
	}
}
