<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Rest;

use WP_Rocket\Tests\Integration\DBTrait;
use WPMedia\PHPUnit\Integration\RESTfulTestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Rest::get_progress
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class GetProgressTest extends RESTfulTestCase {
	use DBTrait;

	private $config;

	public function configTestData() {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		return isset( $this->config['test_data'] )
			? $this->config['test_data']
			: $this->config;
	}

	protected function loadTestDataConfig() {
		$obj      = new \ReflectionObject( $this );
		$filename = $obj->getFileName();

		$this->config = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
	}

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
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->setUpTest( $config );

		$response = $this->doRestRequest( 'GET', '/wp-rocket/v1/rocket-insights/pages/progress', $config['get_data'] );

		$this->assertResponse( $response, $expected );
	}

	private function setUpTest( $config ) {
		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_manage_options' );

		$user = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );

		// Set up database entries if provided
		if ( isset( $config['database_entries'] ) ) {
			foreach ( $config['database_entries'] as $entry ) {
				self::addPerformanceMonitoring( $entry );
			}
		}
	}

	private function assertResponse( $response, $expected ) {
		// Assert the expected response type
		if ( 200 === $expected['code'] ) {
			$this->assertSuccessResponse( $response, $expected );
		} else {
			$this->assertErrorResponse( $response, $expected );
		}
	}

	private function assertSuccessResponse( $response, $expected ) {
		$this->assertTrue( $response['success'] );

		// Check if results are returned
		if ( isset( $expected['results_count'] ) ) {
			$this->assertArrayHasKey( 'results', $response );
			$this->assertCount( $expected['results_count'], $response['results'] );
		}

		// Check if global_score_data is present
		if ( isset( $expected['has_global_score_data'] ) && $expected['has_global_score_data'] ) {
			$this->assertArrayHasKey( 'global_score_data', $response );
		}

		// Check if each result has HTML
		if ( isset( $expected['results_have_html'] ) && $expected['results_have_html'] ) {
			foreach ( $response['results'] as $result ) {
				// Convert to array if it's an object
				$result_array = (array) $result;
				$this->assertArrayHasKey( 'html', $result_array );
				$this->assertNotEmpty( $result_array['html'] );
			}
		}
	}

	private function assertErrorResponse( $response, $expected ) {
		$this->assertSame( $response['data']['status'], $expected['code'] );

		// Check error message if provided
		if ( isset( $expected['error_message'] ) ) {
			$this->assertStringContainsString( $expected['error_message'], $response['message'] );
		}
	}

	protected function doRestRequest( $method, $route, array $body_params = [] ) {
		$request = new \WP_REST_Request( $method, $route );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );

		if ( ! empty( $body_params ) ) {
			$request->set_query_params( $body_params );
		}

		return rest_do_request( $request )->get_data();
	}
}
