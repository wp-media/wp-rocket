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
	public function declare()
	{
		$this->register_service('rucss_usedcss_table', function ($id) {
			$this->add( $id, UsedCSSTable::class );
		});

		$this->register_service('rucss_database', function ($id) {
			$this->add( $id, Database::class )
				->addArgument( $this->get_internal( 'rucss_usedcss_table' ) );
		});

		$this->register_service('rucss_settings', function ($id) {
			$this->add( $id, Settings::class )
				->addArgument( $this->get_internal( 'options' ) )
				->addArgument( $this->get_internal( 'beacon' ) )
				->addArgument( $this->get_internal( 'rucss_usedcss_table' ) );
		});

		$this->register_service('rucss_used_css_query', function ($id) {
			$this->add( $id, UsedCSSQuery::class );
		});

		$this->register_service('rucss_frontend_api_client', function ($id) {
			$this->add( $id, APIClient::class )
				->addArgument( $this->get_internal( 'options' ) );
		});

		$this->register_service('rucss_queue', function ($id) {
			$this->add( $id, Queue::class );
		});

		$this->register_service('rucss_filesystem', function ($id) {
			$this->add( $id, Filesystem::class )
				->addArgument( rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' ) )
				->addArgument( rocket_direct_filesystem() );
		});

		$this->register_service('rucss_used_css_controller', function ($id) {
			$this->add( $id, UsedCSSController::class )
				->addArgument( $this->get_internal( 'options' ) )
				->addArgument( $this->get_internal( 'rucss_used_css_query' ) )
				->addArgument( $this->get_internal( 'rucss_frontend_api_client' ) )
				->addArgument( $this->get_internal( 'rucss_queue' ) )
				->addArgument( $this->get_internal( 'dynamic_lists_data_manager' ) )
				->addArgument( $this->get_internal( 'rucss_filesystem' ) );
		});

		$this->register_service('rucss_admin_subscriber', function ($id) {
			$this->share( $id, AdminSubscriber::class )
				->addArgument( $this->get_internal( 'rucss_settings' ) )
				->addArgument( $this->get_internal( 'rucss_database' ) )
				->addArgument( $this->get_internal( 'rucss_used_css_controller' ) )
				->addArgument( $this->get_internal( 'rucss_queue' ) );
		});

		$this->register_service('rucss_frontend_subscriber', function ($id) {
			$this->share( $id, FrontendSubscriber::class )
				->addArgument( $this->get_internal( 'rucss_used_css_controller' ) );
		});

		$this->register_service('rucss_cron_subscriber', function ($id) {
			$this->share( $id, CronSubscriber::class )
				->addArgument( $this->get_internal( 'rucss_used_css_controller' ) )
				->addArgument( $this->get_internal( 'rucss_database' ) );
		});
	}
}
