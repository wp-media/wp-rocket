<?php


namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Factory;

use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Context\RetryContext;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobNotFound404;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobSetFail;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\ResetRetryProcess;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\DefaultProcess;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;
use WP_Rocket\Engine\Common\JobManager\Interfaces\ManagerInterface;


class StrategyFactory implements LoggerAwareInterface {
	use LoggerAware;

	/**
     * RUCSS Job Manager.
     *
     * @var ManagerInterface
     */
    private $rucss_manager;

    /**
     * LCP Job Manager.
     *
     * @var ManagerInterface
     */
    private $atf_manager;

	/**
	 * Clock instance.
	 *
	 * @var WPRClock
	 */
	protected $clock;

	/**
	 * Constructor.
	 *
     * @param ManagerInterface $rucss_manager RUCSS Job Manager.
     * @param ManagerInterface $lcp_manager LCP Job Manager.
	 * @param WPRClock      $clock Clock instance.
	 */
	public function __construct( ManagerInterface $rucss_manager, ManagerInterface $atf_manager, WPRClock $clock ) {
		$this->rucss_manager = $rucss_manager;
        $this->atf_manager = $atf_manager;
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
				$strategy = new ResetRetryProcess( $this->rucss_manager, $this->atf_manager );
				break;
			case 500:
			case 422:
			case 404:
			case 401:
				$strategy = new JobSetFail( $this->rucss_manager, $this->atf_manager );
				break;
			default:
				$strategy = new DefaultProcess( $this->rucss_manager, $this->atf_manager, $this->clock );
				break;
		}

		$context = new RetryContext();
		$context->set_strategy( $strategy );
		$context->execute( $row_details, $job_details );
	}
}
