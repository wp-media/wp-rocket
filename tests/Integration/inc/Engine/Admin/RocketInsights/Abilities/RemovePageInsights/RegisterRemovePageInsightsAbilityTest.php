<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Abilities\RemovePageInsights;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering wp-rocket/remove-page-insights ability registration and execution.
 *
 * @group RocketInsights
 * @group Abilities
 * @group AdminOnly
 */
class RegisterRemovePageInsightsAbilityTest extends TestCase {
	use DBTrait;

	private $hook_fired = false;
	private $hook_count = 0;

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

		// Skip test if WordPress version is less than 6.9 (Abilities API not available).
		if ( version_compare( $GLOBALS['wp_version'], '6.9', '<' ) ) {
			$this->markTestSkipped( 'WordPress 6.9+ required for Abilities API' );
		}

		// Clean up data before each test.
		self::truncatePerformanceMonitoringTable();

		// Enable Performance Monitoring for the test.
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Add a hook to capture when rocket_rocket_insights_job_deleted is fired.
		add_action( 'rocket_rocket_insights_job_deleted', [ $this, 'capture_hook_fired' ], 10, 1 );

		$this->hook_fired = false;
		$this->hook_count = 0;
	}

	public function tear_down() {
		// Clean up data after each test.
		self::truncatePerformanceMonitoringTable();

		// Remove filters.
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );
		remove_action( 'rocket_rocket_insights_job_deleted', [ $this, 'capture_hook_fired' ] );

		parent::tear_down();
	}

	/**
	 * Captures when the hook is fired.
	 *
	 * @param int $id The ID of the deleted job.
	 */
	public function capture_hook_fired( $id ) {
		$this->hook_fired = true;
		++$this->hook_count;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedOutput( $config, $expected ) {
		$this->set_up_user( $config['has_permission'] );

		// Add existing items if provided.
		if ( ! empty( $config['existing_items'] ) ) {
			foreach ( $config['existing_items'] as $item ) {
				self::addPerformanceMonitoring( $item );
			}
		}

		// Get the ability.
		$ability = wp_get_ability( 'wp-rocket/remove-page-insights' );

		$this->assertNotNull( $ability, 'Ability should be registered' );

		// Execute the ability with input.
		$result = $ability->execute( $config['input'] ?? null );

		if ( $expected['is_error'] ) {
			$this->assertInstanceOf( 'WP_Error', $result, 'Should return WP_Error when user lacks permission.' );
			return;
		}

		// Check the result structure.
		$this->assertArrayHasKey( 'success', $result );
		$this->assertArrayHasKey( 'error', $result );

		// Assert expected values.
		$this->assertSame( $expected['success'], $result['success'] );

		if ( ! $expected['success'] && isset( $expected['error'] ) ) {
			$this->assertSame( $expected['error'], $result['error'] );
		}

		// Check hook fired status.
		$this->assertSame( $expected['hook_fired'] ?? false, $this->hook_fired );

		// On success, the deletion hook should fire once per removed row.
		if ( $expected['hook_fired'] ?? false ) {
			$this->assertSame( $expected['hook_count'] ?? 1, $this->hook_count );
		}

		// Verify the resulting database state: every monitored row for the URL is gone.
		if ( isset( $expected['database_entries_after'] ) ) {
			$container = apply_filters( 'rocket_container', null );
			$query     = $container->get( 'ri_query' );

			$this->assertCount(
				$expected['database_entries_after'],
				$query->query( [] ),
				'The page should be removed from monitoring.'
			);
		}
	}

	/**
	 * Set up user with or without permission.
	 *
	 * @param bool $has_permission Whether user should have permission.
	 *
	 * @return void
	 */
	private function set_up_user( bool $has_permission ): void {
		$admin = get_role( 'administrator' );

		if ( $has_permission ) {
			$admin->add_cap( 'rocket_manage_options' );
			$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		} else {
			$admin->remove_cap( 'rocket_manage_options' );
			$user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		}

		wp_set_current_user( $user_id );
	}
}
