<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Abilities\RetestPageInsights;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering wp-rocket/retest-page-insights ability registration and execution.
 *
 * @group RocketInsights
 * @group Abilities
 * @group AdminOnly
 */
class RegisterRetestPageInsightsAbilityTest extends TestCase {
	use DBTrait;

	private $hook_fired    = false;
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

		// Skip test if WordPress version is less than 6.9 (Abilities API not available).
		if ( version_compare( $GLOBALS['wp_version'], '6.9', '<' ) ) {
			$this->markTestSkipped( 'WordPress 6.9+ required for Abilities API' );
		}

		// Clean up data before each test.
		self::truncatePerformanceMonitoringTable();

		// Enable Performance Monitoring for the test.
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Add a hook to capture when rocket_rocket_insights_job_retest is fired.
		add_action( 'rocket_rocket_insights_job_retest', [ $this, 'capture_hook_fired' ] );

		$this->hook_fired    = false;
		$this->hook_fired_id = null;
	}

	public function tear_down() {
		// Clean up data after each test.
		self::truncatePerformanceMonitoringTable();

		// Remove filters.
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );
		remove_action( 'rocket_rocket_insights_job_retest', [ $this, 'capture_hook_fired' ] );

		parent::tear_down();
	}

	/**
	 * Captures when the retest hook is fired.
	 *
	 * @param int $row_id The row ID of the job that was retested.
	 */
	public function capture_hook_fired( $row_id ) {
		$this->hook_fired    = true;
		$this->hook_fired_id = $row_id;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedOutput( $config, $expected ) {
		$this->set_up_user( $config['has_permission'] );

		// Add existing items if provided.
		$inserted_ids = [];
		if ( ! empty( $config['existing_items'] ) ) {
			foreach ( $config['existing_items'] as $item ) {
				$inserted_id    = self::addPerformanceMonitoring( $item );
				$inserted_ids[] = $inserted_id;
			}
		}

		// Mock HTTP response if needed.
		if ( $config['mock_http'] ?? false ) {
			add_filter( 'pre_http_request', [ $this, 'mock_http_response' ], 10, 3 );
		}

		// Get the ability.
		$ability = wp_get_ability( 'wp-rocket/retest-page-insights' );

		$this->assertNotNull( $ability, 'Ability should be registered' );

		// Execute the ability with input.
		$result = $ability->execute( $config['input'] ?? null );

		// Remove HTTP mock.
		if ( $config['mock_http'] ?? false ) {
			remove_filter( 'pre_http_request', [ $this, 'mock_http_response' ] );
		}

		if ( $expected['is_error'] ) {
			$this->assertInstanceOf( 'WP_Error', $result, 'Should return WP_Error when user lacks permission.' );
			return;
		}

		// Check the result structure.
		$this->assertArrayHasKey( 'success', $result );
		$this->assertArrayHasKey( 'status', $result );
		$this->assertArrayHasKey( 'error', $result );

		// Assert expected values.
		$this->assertSame( $expected['success'], $result['success'] );
		$this->assertSame( $expected['status'], $result['status'] );

		if ( isset( $expected['error'] ) ) {
			$this->assertSame( $expected['error'], $result['error'] );
		}

		// Check hook fired status.
		$this->assertSame( $expected['hook_fired'] ?? false, $this->hook_fired );

		// Verify the hook was fired with the correct row ID.
		if ( $expected['hook_fired'] ?? false ) {
			$this->assertIsInt( $this->hook_fired_id, 'Hook should be fired with an integer row ID' );

			if ( isset( $expected['hook_fired_id'] ) && ! empty( $inserted_ids ) ) {
				$this->assertSame( (int) $inserted_ids[0], $this->hook_fired_id );
			}
		}

		// Verify stale score fields are cleared after successful retest.
		if ( $expected['stale_fields_cleared'] ?? false ) {
			$container = apply_filters( 'rocket_container', null );
			$ri_query  = $container->get( 'ri_query' );
			$rows      = $ri_query->query(
				[
					'url'       => $config['input']['url'],
					'is_mobile' => true,
				]
			);

			if ( ! empty( $rows ) ) {
				$row = reset( $rows );
				$this->assertSame( '', $row->score, 'Score should be cleared after retest trigger' );
				$this->assertSame( '', $row->report_url, 'Report URL should be cleared after retest trigger' );
				$this->assertSame( 0, (int) $row->is_blurred, 'is_blurred should be cleared after retest trigger' );
			}
		}
	}

	/**
	 * Mock HTTP response for testing.
	 *
	 * @param mixed  $preempt Whether to preempt the request.
	 * @param array  $args    Request arguments.
	 * @param string $url     Request URL.
	 *
	 * @return array Mock response.
	 */
	public function mock_http_response( $preempt, $args, $url ) {
		return [
			'response' => [
				'code' => 200,
			],
			'body'     => json_encode(
				[
					'success' => true,
					'uuid'    => 'test-uuid-retest',
					'code'    => 200,
				]
			),
		];
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
