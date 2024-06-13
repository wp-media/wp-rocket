<?php
namespace WP_Rocket\Engine\HealthCheck;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for health check subscribers
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'health_check',
		'health_check_page_cache',
		'action_scheduler_check',
	];

	/**
	 * Check if the service provider provides a specific service.
	 *
	 * @param string $id The id of the service.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->addShared( 'health_check', HealthCheck::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->addShared( 'health_check_page_cache', PageCache::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()->addShared( 'action_scheduler_check', ActionSchedulerCheck::class )
			->addTag( 'common_subscriber' );
	}
}
