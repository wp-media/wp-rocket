<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\JobManager;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\JobManager\Managers\RUCSSManager;
use WP_Rocket\Engine\Common\JobManager\Managers\AtfManager;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Common\JobManager\Strategy\Factory\StrategyFactory;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSSQuery;
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Common\JobManager\Context\RUCSSContext;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Common\JobManager\Queue;
use WP_Rocket\Engine\Common\JobManager\APIHandler\APIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber as CronSubscriber;


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
        'rucss_retry_strategy_factory',
        'rucss_filesystem',
        'job_processor',
        'rucss_context',
        'atf_context',
        'queue',
        'api_client',
        'rucss_usedcss_table',
        'rucss_database',
        'cron_subscriber',
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

        $this->getContainer()->add( 'rucss_retry_strategy_factory', StrategyFactory::class )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_query' ) )
			->addArgument( $this->getContainer()->get( 'wpr_clock' ) );

        $this->getContainer()->add( 'rucss_filesystem', Filesystem::class )
			->addArgument( rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' ) )
			->addArgument( rocket_direct_filesystem() );

		$this->getContainer()->add( 'atf_query', ATFQuery::class );
		$this->getContainer()->add( 'RUCSSContext', RUCSSContext::class );

        $this->getContainer()->add( 'rucss_context', RUCSSContext::class )
            ->addArgument( $this->getContainer()->get( 'options' ) );

        $this->getContainer()->add( 'atf_context', Context::class );

        $this->getContainer()->add( 'rucss_manager', RUCSSManager::class )
            ->addArgument( $this->getContainer()->get( 'rucss_used_css_query' ) )
			->addArgument( $this->getContainer()->get( 'rucss_filesystem' ) )
			->addArgument( $this->getContainer()->get( 'rucss_context' ) );

        $this->getContainer()->add( 'atf_manager', AtfManager::class )
            ->addArgument( $this->getContainer()->get( 'atf_query' ) )
            ->addArgument( $this->getContainer()->get( 'atf_context' ) );

        $this->getContainer()->add( 'queue', Queue::class );

        $this->getContainer()->add( 'api_client', APIClient::class )
            ->addArgument( $this->getContainer()->get( 'options' ) )
            ->addArgument( $this->getContainer()->get( 'rucss_context' ) )
            ->addArgument( $this->getContainer()->get( 'atf_context' ) );

        $this->getContainer()->add( 'job_processor', JobProcessor::class )
            ->addArgument( $this->getContainer()->get( 'rucss_manager' ) )
            ->addArgument( $this->getContainer()->get( 'atf_manager' ) )
            ->addArgument( $this->getContainer()->get( 'queue' ) )
            ->addArgument( $this->getContainer()->get( 'rucss_retry_strategy_factory' ) )
            ->addArgument( $this->getContainer()->get( 'options' ) )
            ->addArgument( $this->getContainer()->get( 'api_client' ) );

        $this->getContainer()->share( 'cron_subscriber', CronSubscriber::class )
			->addArgument( $this->getContainer()->get( 'job_processor' ) )
			->addArgument( $this->getContainer()->get( 'rucss_database' ) )
			->addArgument( $this->getContainer()->get( 'rucss_context' ) )
			->addArgument( $this->getContainer()->get( 'atf_context' ) );
	}
}
