<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::get_results
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class GetResultsTest extends AjaxTestCase {
	use DBTrait;

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
		add_filter( 'rocket_performance_monitoring_enabled', '__return_true' );

		// Set the AJAX action
		$this->action = 'rocket_rocket_insights_get_results';
	}

	public function tear_down() {
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		// Remove Performance Monitoring enabled filter
		remove_filter( 'rocket_performance_monitoring_enabled', '__return_true' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->setUpTest( $config );

		$this->executeAjaxCall();

		$this->assertResponse( $expected );
	}

	private function setUpTest( $config ) {
		// Set up the nonce
		$_GET['nonce'] = \wp_create_nonce( 'rocket-ajax' );

		// Set up database entries if provided
		if ( isset( $config['database_entries'] ) ) {
			foreach ( $config['database_entries'] as $entry ) {
				self::addPerformanceMonitoring( $entry );
			}
		}

		// Set up GET data if provided
		if ( isset( $config['get_data'] ) ) {
			foreach ( $config['get_data'] as $key => $value ) {
				$_GET[ $key ] = $value;
			}
		}
	}

	private function executeAjaxCall() {
		// Try to make the AJAX call
		try {
			$this->_handleAjax( $this->action );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected for successful AJAX responses
		} catch ( \WPAjaxDieStopException $e ) {
			// Expected for error responses
		}
	}

	private function assertResponse( $expected ) {
		// Get the response
		$response = json_decode( $this->_last_response, true );

		// Assert the expected response type
		if ( $expected['success'] ) {
			$this->assertSuccessResponse( $response, $expected );
		} else {
			$this->assertErrorResponse( $response, $expected );
		}
	}

	private function assertSuccessResponse( $response, $expected ) {
		$this->assertTrue( $response['success'] );

		// Check if results are returned
		if ( isset( $expected['results_count'] ) ) {
			$this->assertArrayHasKey( 'results', $response['data'] );
			$this->assertCount( $expected['results_count'], $response['data']['results'] );
		}

		// Check if global_score_data is present
		if ( isset( $expected['has_global_score_data'] ) && $expected['has_global_score_data'] ) {
			$this->assertArrayHasKey( 'global_score_data', $response['data'] );
		}

		// Check if each result has HTML
		if ( isset( $expected['results_have_html'] ) && $expected['results_have_html'] ) {
			foreach ( $response['data']['results'] as $result ) {
				// Convert to array if it's an object
				$result_array = (array) $result;
				$this->assertArrayHasKey( 'html', $result_array );
				$this->assertNotEmpty( $result_array['html'] );
			}
		}
	}

	private function assertErrorResponse( $response, $expected ) {
		$this->assertFalse( $response['success'] );

		// Check error message if provided
		if ( isset( $expected['error_message'] ) ) {
			$this->assertStringContainsString( $expected['error_message'], $response['data']['results'] );
		}
	}
}
