<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Rest;

use WP_Rocket\Tests\Integration\DBTrait;
use WPMedia\PHPUnit\Integration\RESTfulTestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Rest::update_item
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class UpdateItemTest extends RESTfulTestCase {
	use DBTrait;

	private $config;
	private $hook_fired = false;
	private $hook_fired_id = null;

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

		// Add a hook to capture when rocket_rocket_insights_job_retest is fired
		add_action( 'rocket_rocket_insights_job_retest', [ $this, 'capture_hook_fired' ] );

		$this->hook_fired = false;
		$this->hook_fired_id = null;
	}

	public function tear_down() {
		delete_option( 'wp_rocket_pm_credit' );
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		// Remove Performance Monitoring enabled filter
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Remove our test hook
		remove_action( 'rocket_rocket_insights_job_retest', [ $this, 'capture_hook_fired' ] );

		wp_set_current_user( null );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->setUpTest( $config );

		$response = $this->doRestRequest( 'PATCH', '/wp-rocket/v1/rocket-insights/pages/' . $config['id'] );

		$this->assertResponse( $response, $expected );
	}

	private function setUpTest( $config ) {
		if ( ! empty( $config['credit'] ) ) {
			add_option( 'wp_rocket_pm_credit', $config['credit'] );
		}
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
				$this->assertArrayHasKey( $key, $response );
				if ( $value !== null ) {
					$this->assertSame( $value, $response[ $key ] );
				}
			}
		}

		// Check that response contains expected keys
		if ( isset( $expected['response_keys'] ) ) {
			foreach ( $expected['response_keys'] as $key ) {
				$this->assertArrayHasKey( $key, $response );
			}
		}
	}

	private function assertErrorResponse( $response, $expected ) {
		$this->assertSame( $response['data']['status'], $expected['code'] );

		// Check error message if provided
		if ( isset( $expected['error_message'] ) ) {
			$this->assertStringContainsString( $expected['error_message'], $response['message'] );
		}

		// Check if hook was NOT fired for error cases
		if ( isset( $expected['hook_fired'] ) && ! $expected['hook_fired'] ) {
			$this->assertFalse( $this->hook_fired );
		}
	}

	/**
	 * Callback to capture when rocket_rocket_insights_job_retest hook is fired.
	 *
	 * @param int $id The database row ID of the reset job.
	 */
	public function capture_hook_fired( $id ) {
		$this->hook_fired = true;
		$this->hook_fired_id = $id;
	}
}
