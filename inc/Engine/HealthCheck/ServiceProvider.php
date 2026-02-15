<?php
declare(strict_types=1);

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
			->addArgument( 'options' );
		$this->getContainer()->addShared( 'action_scheduler_check', ActionSchedulerCheck::class );
	}
}
