<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PMQuery;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Tables\PerformanceMonitoring as PMTable;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\APIHandler\APIClient as PMAPIClient;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs\Factory as PMFactory;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs\Manager as PMManager;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue\Queue as PMQueue;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\ServiceProvider;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\ServiceProvider
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class Test_ServiceProvider extends TestCase {

	public function testShouldProvideCorrectServices() {
		$service_provider = new ServiceProvider();

		// Test all services are provided
		$this->assertTrue( $service_provider->provides( 'pm_table' ) );
		$this->assertTrue( $service_provider->provides( 'pm_query' ) );
		$this->assertTrue( $service_provider->provides( 'pm_api_client' ) );
		$this->assertTrue( $service_provider->provides( 'pm_context' ) );
		$this->assertTrue( $service_provider->provides( 'pm_manager' ) );
		$this->assertTrue( $service_provider->provides( 'pm_factory' ) );
		$this->assertTrue( $service_provider->provides( 'pm_queue' ) );
		$this->assertTrue( $service_provider->provides( 'pm_subscriber' ) );

		// Test non-existent service returns false
		$this->assertFalse( $service_provider->provides( 'non_existent_service' ) );
	}

	public function testShouldRegisterServicesInContainer() {
		$container = apply_filters( 'rocket_container', null );

		// Test that all services are available in the container.
		$this->assertTrue( $container->has( 'pm_table' ) );
		$this->assertTrue( $container->has( 'pm_query' ) );
		$this->assertTrue( $container->has( 'pm_api_client' ) );
		$this->assertTrue( $container->has( 'pm_context' ) );
		$this->assertTrue( $container->has( 'pm_manager' ) );
		$this->assertTrue( $container->has( 'pm_factory' ) );
		$this->assertTrue( $container->has( 'pm_queue' ) );
		$this->assertTrue( $container->has( 'pm_subscriber' ) );

		// Test that we can retrieve the services and they are of correct type.
		$pm_table = $container->get( 'pm_table' );
		$pm_query = $container->get( 'pm_query' );
		$pm_api_client = $container->get( 'pm_api_client' );
		$pm_context = $container->get( 'pm_context' );
		$pm_manager = $container->get( 'pm_manager' );
		$pm_factory = $container->get( 'pm_factory' );
		$pm_queue = $container->get( 'pm_queue' );
		$pm_subscriber = $container->get( 'pm_subscriber' );

		$this->assertInstanceOf( PMTable::class, $pm_table );
		$this->assertInstanceOf( PMQuery::class, $pm_query );
		$this->assertInstanceOf( PMAPIClient::class, $pm_api_client );
		$this->assertInstanceOf( PerformanceMonitoringContext::class, $pm_context );
		$this->assertInstanceOf( PMManager::class, $pm_manager );
		$this->assertInstanceOf( PMFactory::class, $pm_factory );
		$this->assertInstanceOf( PMQueue::class, $pm_queue );
		$this->assertInstanceOf( Subscriber::class, $pm_subscriber );
	}

	public function testShouldHaveSharedTableService() {
		$container = apply_filters( 'rocket_container', null );

		// Get the table service twice.
		$pm_table_1 = $container->get( 'pm_table' );
		$pm_table_2 = $container->get( 'pm_table' );

		// Should be the same instance (shared).
		$this->assertSame( $pm_table_1, $pm_table_2 );
	}

	public function testShouldHaveSharedFactoryService() {
		$container = apply_filters( 'rocket_container', null );

		// Get the factory service twice.
		$pm_factory_1 = $container->get( 'pm_factory' );
		$pm_factory_2 = $container->get( 'pm_factory' );

		// Should be the same instance (shared).
		$this->assertSame( $pm_factory_1, $pm_factory_2 );
		$this->assertInstanceOf( PMFactory::class, $pm_factory_1 );
	}

	public function testShouldHaveNewQueryInstanceEachTime() {
		$container = apply_filters( 'rocket_container', null );

		// Get the query service twice.
		$pm_query_1 = $container->get( 'pm_query' );
		$pm_query_2 = $container->get( 'pm_query' );

		// Should be different instances (not shared).
		$this->assertNotSame( $pm_query_1, $pm_query_2 );
		$this->assertInstanceOf( PMQuery::class, $pm_query_1 );
		$this->assertInstanceOf( PMQuery::class, $pm_query_2 );
	}

	public function testShouldHaveNewInstancesForNonSharedServices() {
		$container = apply_filters( 'rocket_container', null );

		// Test API Client - should be different instances.
		$pm_api_client_1 = $container->get( 'pm_api_client' );
		$pm_api_client_2 = $container->get( 'pm_api_client' );
		$this->assertNotSame( $pm_api_client_1, $pm_api_client_2 );
		$this->assertInstanceOf( PMAPIClient::class, $pm_api_client_1 );

		// Test Context - should be different instances.
		$pm_context_1 = $container->get( 'pm_context' );
		$pm_context_2 = $container->get( 'pm_context' );
		$this->assertNotSame( $pm_context_1, $pm_context_2 );
		$this->assertInstanceOf( PerformanceMonitoringContext::class, $pm_context_1 );

		// Test Manager - should be different instances.
		$pm_manager_1 = $container->get( 'pm_manager' );
		$pm_manager_2 = $container->get( 'pm_manager' );
		$this->assertNotSame( $pm_manager_1, $pm_manager_2 );
		$this->assertInstanceOf( PMManager::class, $pm_manager_1 );

		// Test Queue - should be different instances.
		$pm_queue_1 = $container->get( 'pm_queue' );
		$pm_queue_2 = $container->get( 'pm_queue' );
		$this->assertNotSame( $pm_queue_1, $pm_queue_2 );
		$this->assertInstanceOf( PMQueue::class, $pm_queue_1 );

		// Test Subscriber - should be one instance.
		$pm_subscriber_1 = $container->get( 'pm_subscriber' );
		$pm_subscriber_2 = $container->get( 'pm_subscriber' );
		$this->assertSame( $pm_subscriber_1, $pm_subscriber_2 );
		$this->assertInstanceOf( Subscriber::class, $pm_subscriber_1 );
	}

	public function testShouldEnsureTableIsCreated() {
		$container = apply_filters( 'rocket_container', null );
		$pm_table = $container->get( 'pm_table' );

		// The table should be accessible and instantiated when the service provider is registered.
		$this->assertNotNull( $pm_table );
		$this->assertIsString( $pm_table->get_name() );

		// Verify we can get table information
		$table_name = $pm_table->get_name();
		$this->assertStringContainsString( 'wpr_performance_monitoring', $table_name );
	}

	public function testShouldConfigureServiceDependencies() {
		$container = apply_filters( 'rocket_container', null );

		// Test that services with dependencies are properly configured.
		// API Client should have options dependency.
		$pm_api_client = $container->get( 'pm_api_client' );
		$this->assertInstanceOf( PMAPIClient::class, $pm_api_client );

		// Context should have options dependency.
		$pm_context = $container->get( 'pm_context' );
		$this->assertInstanceOf( PerformanceMonitoringContext::class, $pm_context );

		// Manager should have query, api_client, context, and options dependencies.
		$pm_manager = $container->get( 'pm_manager' );
		$this->assertInstanceOf( PMManager::class, $pm_manager );

		// Factory should have manager and table dependencies.
		$pm_factory = $container->get( 'pm_factory' );
		$this->assertInstanceOf( PMFactory::class, $pm_factory );

		// Subscriber should have queue, context, and query dependencies.
		$pm_subscriber = $container->get( 'pm_subscriber' );
		$this->assertInstanceOf( Subscriber::class, $pm_subscriber );
	}

	public function testShouldProvideAllRegisteredServices() {
		$service_provider = new ServiceProvider();
		$expected_services = [
			'pm_table',
			'pm_query',
			'pm_api_client',
			'pm_context',
			'pm_manager',
			'pm_factory',
			'pm_queue',
			'pm_subscriber',
		];

		foreach ( $expected_services as $service_id ) {
			$this->assertTrue(
				$service_provider->provides( $service_id ),
				"Service provider should provide service: {$service_id}"
			);
		}
	}
}
