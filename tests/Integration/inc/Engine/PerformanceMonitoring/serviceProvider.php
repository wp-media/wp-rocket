<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\PerformanceMonitoring;

use WP_Rocket\Engine\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PMQuery;
use WP_Rocket\Engine\PerformanceMonitoring\Database\Tables\PerformanceMonitoring as PMTable;
use WP_Rocket\Engine\PerformanceMonitoring\ServiceProvider;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\PerformanceMonitoring\ServiceProvider
 *
 * @group PerformanceMonitoring
 */
class Test_ServiceProvider extends TestCase {

	public function testShouldProvideCorrectServices() {
		$service_provider = new ServiceProvider();

		$this->assertTrue( $service_provider->provides( 'pm_table' ) );
		$this->assertTrue( $service_provider->provides( 'pm_query' ) );
		$this->assertFalse( $service_provider->provides( 'non_existent_service' ) );
	}

	public function testShouldRegisterServicesInContainer() {
		$container = apply_filters( 'rocket_container', null );

		// Test that services are available in the container.
		$this->assertTrue( $container->has( 'pm_table' ) );
		$this->assertTrue( $container->has( 'pm_query' ) );

		// Test that we can retrieve the services.
		$pm_table = $container->get( 'pm_table' );
		$pm_query = $container->get( 'pm_query' );

		$this->assertInstanceOf( PMTable::class, $pm_table );
		$this->assertInstanceOf( PMQuery::class, $pm_query );
	}

	public function testShouldHaveSharedTableService() {
		$container = apply_filters( 'rocket_container', null );

		// Get the table service twice.
		$pm_table_1 = $container->get( 'pm_table' );
		$pm_table_2 = $container->get( 'pm_table' );

		// Should be the same instance (shared).
		$this->assertSame( $pm_table_1, $pm_table_2 );
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
}
