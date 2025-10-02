<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::delete_row
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class DeleteRowTest extends TestCase {
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
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Add a hook to capture when rocket_rocket_insights_job_deleted is fired
		add_action( 'rocket_rocket_insights_job_deleted', [ $this, 'capture_hook_fired' ] );

		$this->hook_fired = false;
		$this->hook_fired_id = null;
	}

	public function tear_down() {
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		// Remove Performance Monitoring enabled filter
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Remove our test hook
		remove_action( 'rocket_rocket_insights_job_deleted', [ $this, 'capture_hook_fired' ] );

		// Clean up GET parameters
		unset( $_GET['_wpnonce'], $_GET['id'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->setUpTest( $config );

		$this->executeAction();

		$this->assertResult( $expected );
	}

	private function setUpTest( $config ) {
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

		// Set up nonce if valid_nonce is specified
		if ( isset( $config['valid_nonce'] ) && $config['valid_nonce'] ) {
			$_GET['_wpnonce'] = \wp_create_nonce( 'delete_rocket_insights_url' );
		}

		// Set up filters if provided
		if ( isset( $config['filters'] ) ) {
			foreach ( $config['filters'] as $filter => $callback ) {
				add_filter( $filter, $callback );
			}
		}

		// Mock wp_get_referer to prevent redirect issues in tests
		add_filter( 'wp_get_referer', [ $this, 'mock_referer' ] );
	}

	private function executeAction() {
		// Capture output to prevent redirect from breaking tests
		ob_start();

		try {
			do_action( 'admin_post_rocket_rocket_insights_delete' );
		} catch ( \WPDieException $e ) {
			// Expected for wp_die() calls
		}

		ob_end_clean();
	}

	private function assertResult( $expected ) {
		// Check if hook was fired
		if ( isset( $expected['hook_fired'] ) ) {
			$this->assertSame( $expected['hook_fired'], $this->hook_fired );

			if ( $expected['hook_fired'] && isset( $expected['hook_fired_id'] ) ) {
				$this->assertSame( $expected['hook_fired_id'], $this->hook_fired_id );
			}
		}

		// Check database state
		if ( isset( $expected['database_entries_after'] ) ) {
			$container = apply_filters( 'rocket_container', null );
			$ri_query = $container->get( 'ri_query' );
			$items = $ri_query->query( [] );
			$this->assertSame( $expected['database_entries_after'], count( $items ) );
		}

		// Check if specific item was deleted
		if ( isset( $expected['item_deleted_id'] ) ) {
			$container = apply_filters( 'rocket_container', null );
			$ri_query = $container->get( 'ri_query' );
			$item = $ri_query->get_row_by_id( $expected['item_deleted_id'] );
			$this->assertFalse( $item );
		}

		// Check if item still exists
		if ( isset( $expected['item_exists_id'] ) ) {
			$container = apply_filters( 'rocket_container', null );
			$ri_query = $container->get( 'ri_query' );
			$item = $ri_query->get_row_by_id( $expected['item_exists_id'] );
			$this->assertNotFalse( $item );
		}
	}

	/**
	 * Callback to capture when rocket_rocket_insights_job_deleted hook is fired.
	 *
	 * @param int $id The ID of the deleted performance monitoring job.
	 */
	public function capture_hook_fired( $id ) {
		$this->hook_fired = true;
		$this->hook_fired_id = $id;
	}

	/**
	 * Mock wp_get_referer to return a test URL.
	 *
	 * @return string
	 */
	public function mock_referer() {
		return 'http://example.org/wp-admin/admin.php?page=wprocket';
	}
}
