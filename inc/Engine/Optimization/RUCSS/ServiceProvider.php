<?php
namespace WP_Rocket\Engine\Optimization\RUCSS;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber as AdminSubscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Queue;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS as UsedCSSController;
use WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber as CronSubscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSSQuery;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS as UsedCSSTable;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber as FrontendSubscriber;

/**
 * Service provider for the WP Rocket RUCSS
 *
 * @since  3.9
 */
class ServiceProvider extends AbstractServiceProvider {

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {

		$this->add( 'rucss_usedcss_table', UsedCSSTable::class );
		$this->add( 'rucss_database', Database::class )
			->addArgument( $this->get_internal( 'rucss_usedcss_table' ) );

		$this->add( 'rucss_settings', Settings::class )
			->addArgument( $this->get_internal( 'options' ) )
			->addArgument( $this->get_internal( 'beacon' ) )
			->addArgument( $this->get_internal( 'rucss_usedcss_table' ) );

		$this->add( 'rucss_used_css_query', UsedCSSQuery::class );
		$this->add( 'rucss_frontend_api_client', APIClient::class )
			->addArgument( $this->get_internal( 'options' ) );
		$this->add( 'rucss_queue', Queue::class );
		$this->add( 'rucss_filesystem', Filesystem::class )
			->addArgument( rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' ) )
			->addArgument( rocket_direct_filesystem() );
		$this->add( 'rucss_used_css_controller', UsedCSSController::class )
			->addArgument( $this->get_internal( 'options' ) )
			->addArgument( $this->get_internal( 'rucss_used_css_query' ) )
			->addArgument( $this->get_internal( 'rucss_frontend_api_client' ) )
			->addArgument( $this->get_internal( 'rucss_queue' ) )
			->addArgument( $this->get_internal( 'dynamic_lists_data_manager' ) )
			->addArgument( $this->get_internal( 'rucss_filesystem' ) );

		$this->share( 'rucss_admin_subscriber', AdminSubscriber::class )
			->addArgument( $this->get_internal( 'rucss_settings' ) )
			->addArgument( $this->get_internal( 'rucss_database' ) )
			->addArgument( $this->get_internal( 'rucss_used_css_controller' ) )
			->addArgument( $this->get_internal( 'rucss_queue' ) );
		$this->share( 'rucss_frontend_subscriber', FrontendSubscriber::class )
			->addArgument( $this->get_internal( 'rucss_used_css_controller' ) );
		$this->share( 'rucss_cron_subscriber', CronSubscriber::class )
			->addArgument( $this->get_internal( 'rucss_used_css_controller' ) )
			->addArgument( $this->get_internal( 'rucss_database' ) );
	}
}
