<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\PerformanceMonitoring;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\PerformanceMonitoring\Database\Tables\PerformanceMonitoring as PMTable;
use WP_Rocket\Engine\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PMQuery;

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
		'pm_table',
		'pm_query',
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
	 * Registers the classes in the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->addShared( 'pm_table', PMTable::class );
		$this->getContainer()->add( 'pm_query', PMQuery::class );

		// Ensure the table is created.
		$this->getContainer()->get( 'pm_table' );
	}
}