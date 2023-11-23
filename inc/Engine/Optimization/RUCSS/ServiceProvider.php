<?php
namespace WP_Rocket\Engine\Optimization\RUCSS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\{Database, OptionSubscriber, Settings};
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber as AdminSubscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Context\RUCSSContext;
use WP_Rocket\Engine\Optimization\RUCSS\Context\RUCSSOptimizeContext;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Queue;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS as UsedCSSController;
use WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber as CronSubscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSSQuery;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS as UsedCSSTable;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber as FrontendSubscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Context\RetryContext;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Factory\StrategyFactory;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\DefaultProcess;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\JobSetFail;
use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\ResetRetryProcess;

/**
 * Service provider for the WP Rocket RUCSS
 *
 * @since  3.9
 */
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
		'rucss_settings',
		'rucss_database',
		'rucss_option_subscriber',
		'rucss_admin_subscriber',
		'rucss_frontend_api_client',
		'rucss_used_css',
		'rucss_used_css_query',
		'rucss_frontend_subscriber',
		'rucss_queue',
		'rucss_filesystem',
		'rucss_cron_subscriber',
		'rucss_used_css_controller',
		'rucss_retry_strategy_factory',
		'rucss_retry_strategy_job_found_no_result',
		'rucss_retry_strategy_job_not_found',
		'rucss_retry_strategy_reset_retry',
		'rucss_retry_strategy_context',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {

		$this->getContainer()->share( 'rucss_usedcss_table', UsedCSSTable::class );
		$this->getContainer()->add( 'rucss_database', Database::class )
			->addArgument( $this->getContainer()->get( 'rucss_usedcss_table' ) );

		$this->getContainer()->add( 'rucss_settings', Settings::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addArgument( $this->getContainer()->get( 'rucss_usedcss_table' ) );

		$this->getContainer()->add( 'rucss_used_css_query', UsedCSSQuery::class );
		$this->getContainer()->add( 'rucss_frontend_api_client', APIClient::class )
			->addArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'rucss_queue', Queue::class );
		$this->getContainer()->add( 'rucss_filesystem', Filesystem::class )
			->addArgument( rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' ) )
			->addArgument( rocket_direct_filesystem() );

		$this->getContainer()->add( 'rucss_context', RUCSSContext::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'rucss_filesystem' ) );

		$this->getContainer()->add( 'rucss_optimize_context', RUCSSOptimizeContext::class )
			->addArgument( $this->getContainer()->get( 'options' ) );

		$this->getContainer()->add( 'wpr_clock', WPRClock::class );

		$this->getContainer()->add( 'rucss_retry_strategy_factory', StrategyFactory::class )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_query' ) )
			->addArgument( $this->getContainer()->get( 'wpr_clock' ) );

		$this->getContainer()->add( 'rucss_used_css_controller', UsedCSSController::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_query' ) )
			->addArgument( $this->getContainer()->get( 'rucss_frontend_api_client' ) )
			->addArgument( $this->getContainer()->get( 'rucss_queue' ) )
			->addArgument( $this->getContainer()->get( 'dynamic_lists_defaultlists_data_manager' ) )
			->addArgument( $this->getContainer()->get( 'rucss_filesystem' ) )
			->addArgument( $this->getContainer()->get( 'rucss_context' ) )
			->addArgument( $this->getContainer()->get( 'rucss_optimize_context' ) )
			->addArgument( $this->getContainer()->get( 'rucss_retry_strategy_factory' ) )
			->addArgument( $this->getContainer()->get( 'wpr_clock' ) );

		$this->getContainer()->add( 'rucss_retry_strategy_default_process', DefaultProcess::class )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_query' ) );

		$this->getContainer()->add( 'rucss_retry_strategy_job_set_fail', JobSetFail::class )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_query' ) );

		$this->getContainer()->add( 'rucss_retry_strategy_reset_retry', ResetRetryProcess::class )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_query' ) );

		$this->getContainer()->add( 'rucss_retry_strategy_context', RetryContext::class );

		$this->getContainer()->share( 'rucss_option_subscriber', OptionSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rucss_settings' ) );
		$this->getContainer()->share( 'rucss_admin_subscriber', AdminSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rucss_settings' ) )
			->addArgument( $this->getContainer()->get( 'rucss_database' ) )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_controller' ) )
			->addArgument( $this->getContainer()->get( 'rucss_queue' ) );
		$this->getContainer()->share( 'rucss_frontend_subscriber', FrontendSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_controller' ) )
			->addArgument( $this->getContainer()->get( 'rucss_context' ) );
		$this->getContainer()->share( 'rucss_cron_subscriber', CronSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_controller' ) )
			->addArgument( $this->getContainer()->get( 'rucss_database' ) );
	}
}
