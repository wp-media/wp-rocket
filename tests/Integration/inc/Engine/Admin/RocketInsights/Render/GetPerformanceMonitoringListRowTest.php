<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Render;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Render::get_performance_monitoring_list_row
 *
 * Specifically tests the new functionality where "Analyzing your page (~1 min)" is shown
 * instead of "xx seconds ago" when a URL is being tested.
 *
 * @group RocketInsights
 */
class Test_GetPerformanceMonitoringListRow extends TestCase {
	use DBTrait;

	protected static $container;
	private $render;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install the Performance Monitoring table.
		self::installPerformanceMonitoringTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPerformanceMonitoringTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		// Clean up data before each test
		self::truncatePerformanceMonitoringTable();

		// Enable Performance Monitoring for the test
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Get container and render instance
		self::$container = apply_filters( 'rocket_container', null );
		$this->render = self::$container->get( 'pm_render' );
	}

	public function tear_down() {
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		// Remove Performance Monitoring enabled filter
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		// Add row to database
		$row_id = $this->addPerformanceMonitoring( $config['row_data'] );

		// Get the row object
		$query = self::$container->get( 'ri_query' );
		$row = $query->get_item( $row_id );

		// Generate the HTML
		$html = $this->render->get_performance_monitoring_list_row( $row );

		// Assert the expected content based on fixture data
		if ( isset( $expected['should_show_analyzing'] ) ) {
			if ( $expected['should_show_analyzing'] ) {
				$this->assertStringContainsString( 'Analyzing your page (~1 min)', $html );
				$this->assertStringNotContainsString( 'ago', $html );
			} else {
				$this->assertStringNotContainsString( 'Analyzing your page (~1 min)', $html );
				$this->assertStringContainsString( 'ago', $html );
			}
		}

		// Check for contains strings
		if ( isset( $expected['contains'] ) ) {
			foreach ( $expected['contains'] as $string ) {
				$this->assertStringContainsString( $string, $html );
			}
		}

		// Check for not contains strings
		if ( isset( $expected['not_contains'] ) ) {
			foreach ( $expected['not_contains'] as $string ) {
				$this->assertStringNotContainsString( $string, $html );
			}
		}

		// Additional standard assertions for all rows
		$this->assertStringContainsString( $config['row_data']['url'], $html );
		$this->assertStringContainsString( $config['row_data']['title'], $html );
		$this->assertStringContainsString( 'data-rocket-insights-id="' . $row_id . '"', $html );
	}
}
