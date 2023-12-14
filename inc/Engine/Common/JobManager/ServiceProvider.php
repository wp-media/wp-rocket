<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\JobManager;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\JobManager\Strategy\Context\RetryContext;
use WP_Rocket\Engine\Common\JobManager\Strategy\Factory\StrategyFactory;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\DefaultProcess;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\JobSetFail;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\ResetRetryProcess;
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
use WP_Rocket\Engine\Common\JobManager\APIHandler\APIClient;
use WP_Rocket\Engine\Common\JobManager\Cron\Subscriber as CronSubscriber;


class ServiceProvider extends AbstractServiceProvider {
	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'wpr_clock',
		'retry_strategy_factory',
		'retry_strategy_context',
		'job_processor',
		'queue',
		'api_client',
		'cron_subscriber',
	];

	/**
	 * Registers the classes in the container
	 *
	 * @return void
	 */
	public function register() {

		$factories = [
			$this->getContainer()->get( 'rucss_factory' ),
			$this->getContainer()->get( 'atf_factory' ),
		];

		$this->getContainer()->add( 'wpr_clock', WPRClock::class );

		$this->getContainer()->add( 'retry_strategy_context', RetryContext::class );

		$this->getContainer()->add( 'retry_strategy_factory', StrategyFactory::class )
			->addArgument( $this->getContainer()->get( 'wpr_clock' ) );

		foreach ( $factories as $factory ) {
			$optimization = $factory->manager()->get_optimization_type();

			$this->getContainer()->add( $optimization . '_retry_strategy_default_process', DefaultProcess::class )
				->addArguments(
					[
						$factory->manager(),
						$this->getContainer()->get( 'wpr_clock' ),
					]
					);

			$this->getContainer()->add( $optimization . '_retry_strategy_job_set_fail', JobSetFail::class )
				->addArgument( $factory->manager() );

			$this->getContainer()->add( $optimization . '_retry_strategy_reset_retry', ResetRetryProcess::class )
				->addArgument( $factory->manager() );
		}

		$this->getContainer()->add( 'queue', Queue::class );

		$this->getContainer()->add( 'api_client', APIClient::class )
		->addArguments(
			[
				$this->getContainer()->get( 'options' ),
			]
			);

		$this->getContainer()->share( 'job_processor', JobProcessor::class )
			->addArguments(
				[
					$factories,
					$this->getContainer()->get( 'queue' ),
					$this->getContainer()->get( 'retry_strategy_factory' ),
					$this->getContainer()->get( 'api_client' ),
					$this->getContainer()->get( 'wpr_clock' ),
				]
		);

		$this->getContainer()->share( 'cron_subscriber', CronSubscriber::class )
			->addArguments(
				[
					$this->getContainer()->get( 'job_processor' ),
					$factories,
				]
				);
	}
}
