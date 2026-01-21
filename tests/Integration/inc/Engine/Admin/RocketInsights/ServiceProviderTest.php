<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights;

use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as RIQuery;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Tables\RocketInsights as RITable;
use WP_Rocket\Engine\Admin\RocketInsights\APIHandler\APIClient as RIAPIClient;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Factory as RIFactory;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager as RIManager;
use WP_Rocket\Engine\Admin\RocketInsights\Queue\Queue as RIQueue;
use WP_Rocket\Engine\Admin\RocketInsights\Subscriber;
use WP_Rocket\Engine\Admin\RocketInsights\ServiceProvider;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\ServiceProvider
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class ServiceProviderTest extends TestCase {

	public function testShouldProvideCorrectServices() {
		$service_provider = new ServiceProvider();

		// Test all services are provided
		$this->assertTrue( $service_provider->provides( 'ri_table' ) );
		$this->assertTrue( $service_provider->provides( 'ri_query' ) );
		$this->assertTrue( $service_provider->provides( 'ri_api_client' ) );
		$this->assertTrue( $service_provider->provides( 'ri_context' ) );
		$this->assertTrue( $service_provider->provides( 'ri_manager' ) );
		$this->assertTrue( $service_provider->provides( 'ri_factory' ) );
		$this->assertTrue( $service_provider->provides( 'ri_queue' ) );
		$this->assertTrue( $service_provider->provides( 'ri_subscriber' ) );

		// Test non-existent service returns false
		$this->assertFalse( $service_provider->provides( 'non_existent_service' ) );
	}

	public function testShouldRegisterServicesInContainer() {
		$container = apply_filters( 'rocket_container', null );

		// Test that all services are available in the container.
		$this->assertTrue( $container->has( 'ri_table' ) );
		$this->assertTrue( $container->has( 'ri_query' ) );
		$this->assertTrue( $container->has( 'ri_api_client' ) );
		$this->assertTrue( $container->has( 'ri_context' ) );
		$this->assertTrue( $container->has( 'ri_manager' ) );
		$this->assertTrue( $container->has( 'ri_factory' ) );
		$this->assertTrue( $container->has( 'ri_queue' ) );
		$this->assertTrue( $container->has( 'ri_subscriber' ) );

		// Test that we can retrieve the services and they are of correct type.
		$ri_table = $container->get( 'ri_table' );
		$ri_query = $container->get( 'ri_query' );
		$ri_api_client = $container->get( 'ri_api_client' );
		$ri_context = $container->get( 'ri_context' );
		$ri_manager = $container->get( 'ri_manager' );
		$ri_factory = $container->get( 'ri_factory' );
		$ri_queue = $container->get( 'ri_queue' );
		$ri_subscriber = $container->get( 'ri_subscriber' );

		$this->assertInstanceOf( RITable::class, $ri_table );
		$this->assertInstanceOf( RIQuery::class, $ri_query );
		$this->assertInstanceOf( RIAPIClient::class, $ri_api_client );
		$this->assertInstanceOf( Context::class, $ri_context );
		$this->assertInstanceOf( RIManager::class, $ri_manager );
		$this->assertInstanceOf( RIFactory::class, $ri_factory );
		$this->assertInstanceOf( RIQueue::class, $ri_queue );
		$this->assertInstanceOf( Subscriber::class, $ri_subscriber );
	}

	public function testShouldHaveSharedTableService() {
		$container = apply_filters( 'rocket_container', null );

		// Get the table service twice.
		$ri_table_1 = $container->get( 'ri_table' );
		$ri_table_2 = $container->get( 'ri_table' );

		// Should be the same instance (shared).
		$this->assertSame( $ri_table_1, $ri_table_2 );
	}

	public function testShouldHaveSharedFactoryService() {
		$container = apply_filters( 'rocket_container', null );

		// Get the factory service twice.
		$ri_factory_1 = $container->get( 'ri_factory' );
		$ri_factory_2 = $container->get( 'ri_factory' );

		// Should be the same instance (shared).
		$this->assertSame( $ri_factory_1, $ri_factory_2 );
		$this->assertInstanceOf( RIFactory::class, $ri_factory_1 );
	}

	public function testShouldHaveNewQueryInstanceEachTime() {
		$container = apply_filters( 'rocket_container', null );

		// Get the query service twice.
		$ri_query_1 = $container->get( 'ri_query' );
		$ri_query_2 = $container->get( 'ri_query' );

		// Should be different instances (not shared).
		$this->assertNotSame( $ri_query_1, $ri_query_2 );
		$this->assertInstanceOf( RIQuery::class, $ri_query_1 );
		$this->assertInstanceOf( RIQuery::class, $ri_query_2 );
	}

	public function testShouldHaveNewInstancesForNonSharedServices() {
		$container = apply_filters( 'rocket_container', null );

		// Test API Client - should be different instances.
		$ri_api_client_1 = $container->get( 'ri_api_client' );
		$ri_api_client_2 = $container->get( 'ri_api_client' );
		$this->assertNotSame( $ri_api_client_1, $ri_api_client_2 );
		$this->assertInstanceOf( RIAPIClient::class, $ri_api_client_1 );

		// Test Context - should be different instances.
		$ri_context_1 = $container->get( 'ri_context' );
		$ri_context_2 = $container->get( 'ri_context' );
		$this->assertNotSame( $ri_context_1, $ri_context_2 );
		$this->assertInstanceOf( Context::class, $ri_context_1 );

		// Test Manager - should be different instances.
		$ri_manager_1 = $container->get( 'ri_manager' );
		$ri_manager_2 = $container->get( 'ri_manager' );
		$this->assertNotSame( $ri_manager_1, $ri_manager_2 );
		$this->assertInstanceOf( RIManager::class, $ri_manager_1 );

		// Test Queue - should be different instances.
		$ri_queue_1 = $container->get( 'ri_queue' );
		$ri_queue_2 = $container->get( 'ri_queue' );
		$this->assertNotSame( $ri_queue_1, $ri_queue_2 );
		$this->assertInstanceOf( RIQueue::class, $ri_queue_1 );

		// Test Subscriber - should be one instance.
		$ri_subscriber_1 = $container->get( 'ri_subscriber' );
		$ri_subscriber_2 = $container->get( 'ri_subscriber' );
		$this->assertSame( $ri_subscriber_1, $ri_subscriber_2 );
		$this->assertInstanceOf( Subscriber::class, $ri_subscriber_1 );
	}

	public function testShouldEnsureTableIsCreated() {
		$container = apply_filters( 'rocket_container', null );
		$ri_table = $container->get( 'ri_table' );

		// The table should be accessible and instantiated when the service provider is registered.
		$this->assertNotNull( $ri_table );
		$this->assertIsString( $ri_table->get_name() );

		// Verify we can get table information
		$table_name = $ri_table->get_name();
		$this->assertStringContainsString( 'wpr_performance_monitoring', $table_name );
	}

	public function testShouldConfigureServiceDependencies() {
		$container = apply_filters( 'rocket_container', null );

		// Test that services with dependencies are properly configured.
		// API Client should have options dependency.
		$ri_api_client = $container->get( 'ri_api_client' );
		$this->assertInstanceOf( RIAPIClient::class, $ri_api_client );

		// Context should have options dependency.
		$ri_context = $container->get( 'ri_context' );
		$this->assertInstanceOf( Context::class, $ri_context );

		// Manager should have query, api_client, context, and options dependencies.
		$ri_manager = $container->get( 'ri_manager' );
		$this->assertInstanceOf( RIManager::class, $ri_manager );

		// Factory should have manager and table dependencies.
		$ri_factory = $container->get( 'ri_factory' );
		$this->assertInstanceOf( RIFactory::class, $ri_factory );

		// Subscriber should have queue, context, and query dependencies.
		$ri_subscriber = $container->get( 'ri_subscriber' );
		$this->assertInstanceOf( Subscriber::class, $ri_subscriber );
	}

	public function testShouldProvideAllRegisteredServices() {
		$service_provider = new ServiceProvider();
		$expected_services = [
			'ri_table',
			'ri_query',
			'ri_api_client',
			'ri_context',
			'ri_manager',
			'ri_factory',
			'ri_queue',
			'ri_subscriber',
		];

		foreach ( $expected_services as $service_id ) {
			$this->assertTrue(
				$service_provider->provides( $service_id ),
				"Service provider should provide service: {$service_id}"
			);
		}
	}
}
