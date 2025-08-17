<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Tables\PerformanceMonitoring as PMTable;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PMQuery;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\APIHandler\APIClient as PMAPIClient;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs\Factory as PMFactory;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs\Manager as PMManager;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue\Queue as PMQueue;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue\Processor as PMProcessor;

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
		'pm_api_client',
		'pm_context',
		'pm_manager',
		'pm_factory',
		'pm_queue',
		'pm_processor',
		'pm_subscriber',
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
		// Database layer.
		$this->getContainer()->addShared( 'pm_table', PMTable::class );
		$this->getContainer()->add( 'pm_query', PMQuery::class );

		// API Client.
		$this->getContainer()->add( 'pm_api_client', PMAPIClient::class )
			->addArgument( 'options' );

		// Context.
		$this->getContainer()->add( 'pm_context', PerformanceMonitoringContext::class )
			->addArgument( 'options' );

		// Jobs layer.
		$this->getContainer()->add( 'pm_manager', PMManager::class )
			->addArguments(
				[
					'pm_query',
					'pm_api_client',
					'pm_context',
					'options',
				]
				);

		$this->getContainer()->addShared( 'pm_factory', PMFactory::class )
			->addArguments(
				[
					'pm_manager',
					'pm_table',
					'pm_api_client',
				]
				);

		// Queue layer.
		$this->getContainer()->add( 'pm_queue', PMQueue::class );

		$this->getContainer()->add( 'pm_processor', PMProcessor::class )
			->addArguments(
				[
					'pm_factory',
					'pm_api_client',
					'pm_queue',
					'pm_query',
				]
				);

		// Subscriber.
		$this->getContainer()->add( 'pm_subscriber', Subscriber::class )
			->addArguments(
				[
					'pm_queue',
					'pm_context',
					'pm_query',
				]
				);

		// Ensure the table is created.
		$this->getContainer()->get( 'pm_table' );
	}
}
