<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber::reset_page
 *
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class ResetPageTest extends AjaxTestCase {
	use DBTrait;

	private $hook_fired = false;
	private $hook_fired_id = null;

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
		$this->action = 'rocket_pm_reset_page';

		// Add a hook to capture when rocket_pm_job_retest is fired
		add_action( 'rocket_pm_job_retest', [ $this, 'capture_hook_fired' ] );

		$this->hook_fired = false;
		$this->hook_fired_id = null;
	}

	public function tear_down() {
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		// Remove Performance Monitoring enabled filter
		remove_filter( 'rocket_performance_monitoring_enabled', '__return_true' );

		// Remove our test hook
		remove_action( 'rocket_pm_job_retest', [ $this, 'capture_hook_fired' ] );

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
		$_POST['nonce'] = \wp_create_nonce( 'rocket-ajax' );

		// Set up database entries if provided
		if ( isset( $config['database_entries'] ) ) {
			foreach ( $config['database_entries'] as $entry ) {
				self::addPerformanceMonitoring( $entry );
			}
		}

		// Set up POST data if provided
		if ( isset( $config['post_data'] ) ) {
			foreach ( $config['post_data'] as $key => $value ) {
				$_POST[ $key ] = $value;
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

		// Check if hook was fired
		if ( isset( $expected['hook_fired'] ) && $expected['hook_fired'] ) {
			$this->assertTrue( $this->hook_fired );

			if ( isset( $expected['hook_fired_id'] ) ) {
				$this->assertSame( $expected['hook_fired_id'], $this->hook_fired_id );
			}
		}

		// Check response data if provided
		if ( isset( $expected['response_data'] ) ) {
			foreach ( $expected['response_data'] as $key => $value ) {
				$this->assertArrayHasKey( $key, $response['data'] );
				if ( $value !== null ) {
					$this->assertSame( $value, $response['data'][ $key ] );
				}
			}
		}

		// Check that response contains expected keys
		if ( isset( $expected['response_keys'] ) ) {
			foreach ( $expected['response_keys'] as $key ) {
				$this->assertArrayHasKey( $key, $response['data'] );
			}
		}
	}

	private function assertErrorResponse( $response, $expected ) {
		$this->assertFalse( $response['success'] );

		// Check error message if provided
		if ( isset( $expected['error_message'] ) ) {
			$this->assertStringContainsString( $expected['error_message'], $response['data']['message'] );
		}

		// Check if hook was NOT fired for error cases
		if ( isset( $expected['hook_fired'] ) && ! $expected['hook_fired'] ) {
			$this->assertFalse( $this->hook_fired );
		}
	}

	/**
	 * Callback to capture when rocket_pm_job_retest hook is fired.
	 *
	 * @param int $id The database row ID of the reset job.
	 */
	public function capture_hook_fired( $id ) {
		$this->hook_fired = true;
		$this->hook_fired_id = $id;
	}
}
