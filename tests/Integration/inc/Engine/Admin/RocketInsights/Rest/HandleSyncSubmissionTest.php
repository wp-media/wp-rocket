<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Rest;

use WP_Rocket\Engine\Admin\RocketInsights\Rest;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Rest::handle_sync_submission
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class HandleSyncSubmissionTest extends TestCase {
	use DBTrait, HttpRequestTrait;

	private $controller;
	private $container;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install the Performance Monitoring table
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

		$this->container  = apply_filters( 'rocket_container', null );
		$this->controller = $this->container->get( 'ri_rest' );

		// A failing API response makes the sync submission fall back to the async queue.
		$this->config['http'] = [
			'http://localhostperformance/' => [
				'response' => [
					'code'    => 500,
					'message' => 'Internal Server Error',
				],
				'body'     => wp_json_encode( [ 'error' => 'API error' ] ),
			],
		];

		$this->setup_http();
	}

	public function tear_down() {
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		// Use reflection to access the private method
		$method = $this->get_reflective_method( 'handle_sync_submission', Rest::class );

		// Call the method
		$result = $method->invoke(
			$this->controller,
			$config['url'],
			$config['is_mobile'],
			$config['additional_details']
		);

		// Assert the result type
		if ( 'int' === $expected['result_type'] ) {
			$this->assertIsInt( $result );
		} elseif ( 'bool' === $expected['result_type'] ) {
			$this->assertIsBool( $result );
		} elseif ( 'null' === $expected['result_type'] ) {
			$this->assertNull( $result );
		}

		// Check database state if specified
		if ( isset( $expected['db_check'] ) ) {
			$query = $this->container->get( 'ri_query' );
			$items = $query->query( [ 'url' => $config['url'] ] );

			if ( $expected['db_check']['exists'] ) {
				$this->assertNotEmpty( $items );

				if ( isset( $expected['db_check']['status'] ) ) {
					$this->assertSame( $expected['db_check']['status'], $items[0]->status );
				}
			} else {
				$this->assertEmpty( $items );
			}
		}
	}
}
