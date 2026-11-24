<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Rest;

use WP_Rocket\Engine\Admin\RocketInsights\Rest;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Rest::handle_sync_submission
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class HandleSyncSubmissionTest extends TestCase {
	use DBTrait;

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
			'https://saas.wp-rocket.me/performance/' => [
				'response' => [
					'code'    => 500,
					'message' => 'Internal Server Error',
				],
				'body'     => wp_json_encode( [ 'error' => 'API error' ] ),
			],
		];
	}

	public function tear_down() {
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		delete_transient( 'wp_rocket_rocket_insights_feature_disabled' );
		remove_filter( 'rocket_rocket_insights_enabled', [ $this, 'return_true' ] );
		remove_filter( 'rocket_rocket_insights_enabled', [ $this, 'return_false' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		add_filter( 'rocket_rocket_insights_enabled', [ $this, $config['enabled'] ? 'return_true' : 'return_false' ], PHP_INT_MAX );

		if ( isset( $config['http'] ) ) {
			$this->config['http'] = $config['http'];
		}

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

		if ( isset( $expected['feature_disabled'] ) ) {
			$this->assertFeatureDisabledBackOff( $config, $expected['feature_disabled'], $method );
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

	/**
	 * Asserts the back-off is stored and that a second submission sends no request.
	 *
	 * @param array             $config   Test config.
	 * @param array             $expected Expected back-off.
	 * @param \ReflectionMethod $method   handle_sync_submission().
	 */
	private function assertFeatureDisabledBackOff( array $config, array $expected, \ReflectionMethod $method ) {
		$until = get_transient( 'wp_rocket_rocket_insights_feature_disabled' );

		$this->assertEqualsWithDelta( time() + $expected['delay'], $until, 2 );

		$method->invoke( $this->controller, $expected['second_url'], $config['is_mobile'], $config['additional_details'] );

		$this->assertCount( 1, $this->get_http_requests( 'https://saas.wp-rocket.me/performance/' ) );

		$query = $this->container->get( 'ri_query' );

		foreach ( [ $config['url'], $expected['second_url'] ] as $url ) {
			$items = $query->query( [ 'url' => $url ] );

			$this->assertNotEmpty( $items );
			$this->assertSame( 'to-submit', $items[0]->status );
		}
	}
}
