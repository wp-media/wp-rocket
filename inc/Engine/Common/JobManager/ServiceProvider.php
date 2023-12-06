<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\JobManager;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\JobManager\Managers\RUCSSManager;
use WP_Rocket\Engine\Common\JobManager\Managers\AtfManager;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Common\JobManager\Strategy\Context\RetryContext;
use WP_Rocket\Engine\Common\JobManager\Strategy\Factory\StrategyFactory;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\DefaultProcess;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\JobSetFail;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\ResetRetryProcess;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSSQuery;
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Common\JobManager\Context\RUCSSContext;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Common\JobManager\Queue;
use WP_Rocket\Engine\Common\JobManager\APIHandler\APIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber as CronSubscriber;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;


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
        'rucss_used_css_query',
		'atf_query',
        'rucss_manager',
        'atf_manager',
        'wpr_clock',
        'retry_strategy_factory',
        'rucss_retry_strategy_default_process',
        'rucss_retry_strategy_job_set_fail',
        'rucss_retry_strategy_reset_retry',
        'atf_retry_strategy_default_process',
        'atf_retry_strategy_job_set_fail',
        'atf_retry_strategy_reset_retry',
        'retry_strategy_context',
        'rucss_filesystem',
        'job_processor',
        'rucss_context',
        'atf_context',
        'queue',
        'api_client',
        'rucss_usedcss_table',
        'rucss_database',
        'cron_subscriber',
        'atf_table',
	];

	/**
	 * Registers the classes in the container
	 *
	 * @return void
	 */
	public function register() {
        $this->getContainer()->share( 'rucss_usedcss_table', UsedCSSTable::class );
        
		$this->getContainer()->add( 'rucss_database', Database::class )
			->addArgument( $this->getContainer()->get( 'rucss_usedcss_table' ) );

        $this->getContainer()->add( 'rucss_used_css_query', UsedCSSQuery::class );
        $this->getContainer()->add( 'wpr_clock', WPRClock::class );

        $this->getContainer()->add( 'atf_query', ATFQuery::class );

        $this->getContainer()->add( 'rucss_context', RUCSSContext::class )
            ->addArgument( $this->getContainer()->get( 'options' ) );

        $this->getContainer()->add( 'atf_context', Context::class );

        $this->getContainer()->add( 'rucss_manager', RUCSSManager::class )
            ->addArguments([
                $this->getContainer()->get( 'rucss_used_css_query' ),
                $this->getContainer()->get( 'rucss_filesystem' ),
                $this->getContainer()->get( 'rucss_context' )
            ]);

        $this->getContainer()->add( 'atf_manager', AtfManager::class )
            ->addArguments([
                $this->getContainer()->get( 'atf_query' ),
                $this->getContainer()->get( 'atf_context' )
            ]);

        $this->getContainer()->add( 'retry_strategy_context', RetryContext::class );

        $this->getContainer()->add( 'retry_strategy_factory', StrategyFactory::class )
			->addArgument( $this->getContainer()->get( 'wpr_clock' ) );

        $this->getContainer()->add( 'rucss_retry_strategy_default_process', DefaultProcess::class )
            ->addArguments([
                $this->getContainer()->get( 'rucss_manager' ),
                $this->getContainer()->get( 'wpr_clock' )
            ]);

        $this->getContainer()->add( 'atf_retry_strategy_default_process', DefaultProcess::class )
            ->addArguments([
                $this->getContainer()->get( 'atf_manager' ),
                $this->getContainer()->get( 'wpr_clock' )
            ]);

        $this->getContainer()->add( 'rucss_retry_strategy_job_set_fail', JobSetFail::class )
            ->addArgument( $this->getContainer()->get( 'rucss_manager' ) );

        $this->getContainer()->add( 'atf_retry_strategy_job_set_fail', JobSetFail::class )
            ->addArgument( $this->getContainer()->get( 'atf_manager' ) );

        $this->getContainer()->add( 'rucss_retry_strategy_reset_retry', ResetRetryProcess::class )
            ->addArgument( $this->getContainer()->get( 'rucss_manager' ) );

        $this->getContainer()->add( 'atf_retry_strategy_reset_retry', ResetRetryProcess::class )
            ->addArgument( $this->getContainer()->get( 'atf_manager' ) );

        $this->getContainer()->add( 'rucss_filesystem', Filesystem::class )
			->addArguments([
                rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' ),
                rocket_direct_filesystem()
            ]);

        $this->getContainer()->add( 'queue', Queue::class );

        $this->getContainer()->add( 'job_processor', JobProcessor::class )
            ->addArguments([
                $this->getContainer()->get( 'rucss_manager' ),
                $this->getContainer()->get( 'atf_manager' ),
                $this->getContainer()->get( 'queue' ),
                $this->getContainer()->get( 'rucss_retry_strategy_factory' ),
                $this->getContainer()->get( 'options' ),
                $this->getContainer()->get( 'api_client' )
            ]);

        $this->getContainer()->add( 'api_client', APIClient::class )
            ->addArguments([
                $this->getContainer()->get( 'options' ),
                $this->getContainer()->get( 'rucss_context' ),
                $this->getContainer()->get( 'atf_context' )
            ]);

        $this->getContainer()->share( 'cron_subscriber', CronSubscriber::class )
            ->addArguments([
                $this->getContainer()->get( 'job_processor' ),
                $this->getContainer()->get( 'rucss_database' ),
                $this->getContainer()->get( 'rucss_context' ),
                $this->getContainer()->get( 'atf_context' )
            ]);
	}
}
