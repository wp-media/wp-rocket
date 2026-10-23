<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\Render;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Render::get_dashboard_recommendations
 *
 * @group RocketInsights
 * @group Recommendations
 * @group AdminOnly
 */
class Test_GetDashboardRecommendations extends TestCase {
	/**
	 * Transient name for global score data.
	 *
	 * @var string
	 */
	private const GLOBAL_SCORE_TRANSIENT = 'wpr_global_score_data';

	/**
	 * Set up before each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$this->installPerformanceMonitoringTable();
		delete_transient( self::GLOBAL_SCORE_TRANSIENT );
	}

	/**
	 * Tear down after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		delete_transient( self::GLOBAL_SCORE_TRANSIENT );
		$this->uninstallPerformanceMonitoringTable();

		parent::tear_down();
	}

	/**
	 * Test that the dashboard recommendations component renders the loading state text.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected values.
	 * @return void
	 */
	public function testShouldRenderLoadingState( $config, $expected ) {
		$this->addTrackedPage();

		$container = apply_filters( 'rocket_container', null );
		$output    = $container->get( 'ri_recommendations_render' )->get_dashboard_recommendations( $config['recommendations'] );

		$this->assertStringContainsString( 'data-state="' . $expected['state'] . '"', $output );

		foreach ( $expected['contains'] as $string ) {
			$this->assertStringContainsString( $string, $output );
		}

		foreach ( $expected['not_contains'] as $string ) {
			$this->assertStringNotContainsString( $string, $output );
		}
	}

	/**
	 * Add a tracked page to the Performance Monitoring table.
	 *
	 * @return void
	 */
	private function addTrackedPage(): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->insert(
			$wpdb->prefix . 'wpr_performance_monitoring',
			[
				'url'           => 'http://example.org',
				'title'         => '',
				'is_mobile'     => 0,
				'job_id'        => 'test_job_123',
				'queue_name'    => '',
				'retries'       => 1,
				'status'        => 'in-progress',
				'data'          => '',
				'modified'      => current_time( 'mysql', true ),
				'last_accessed' => current_time( 'mysql', true ),
			]
		);
	}
}
