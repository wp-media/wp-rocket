<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Abilities\AddPageInsights;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering wp-rocket/add-page-insights ability registration and execution.
 *
 * @group RocketInsights
 * @group Abilities
 * @group AdminOnly
 */
class RegisterAddPageInsightsAbilityTest extends TestCase {
	use DBTrait;

	private $hook_fired = false;
	private $hook_data = [];

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

		// Add a hook to capture when rocket_rocket_insights_job_added is fired.
		add_action( 'rocket_rocket_insights_job_added', [ $this, 'capture_hook_fired' ], 10, 4 );

		$this->hook_fired = false;
		$this->hook_data  = [];
	}

	public function tear_down() {
		// Clean up data after each test.
		self::truncatePerformanceMonitoringTable();

		// Remove filters.
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );
		remove_action( 'rocket_rocket_insights_job_added', [ $this, 'capture_hook_fired' ] );

		parent::tear_down();
	}

	/**
	 * Captures when the hook is fired.
	 *
	 * @param string $url        The URL that was added.
	 * @param string $plan       The plan name.
	 * @param int    $urls_count The number of URLs.
	 * @param string $source     The source of the request.
	 */
	public function capture_hook_fired( $url, $plan, $urls_count, $source ) {
		$this->hook_fired = true;
		$this->hook_data  = [
			'url'        => $url,
			'plan'       => $plan,
			'urls_count' => $urls_count,
			'source'     => $source,
		];
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

		// Mock HTTP response if needed.
		if ( $config['mock_http'] ?? false ) {
			add_filter( 'pre_http_request', [ $this, 'mock_http_response' ], 10, 3 );
		}

		// Set URL limit if provided.
		if ( isset( $config['url_limit'] ) ) {
			add_filter( 'rocket_rocket_insights_max_urls', function () use ( $config ) {
				return $config['url_limit'];
			} );
		}

		// Get the ability.
		$ability = wp_get_ability( 'wp-rocket/add-page-insights' );

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
		$this->assertArrayHasKey( 'error', $result );

		// Assert expected values.
		$this->assertSame( $expected['success'], $result['success'] );

		if ( ! $expected['success'] && isset( $expected['error'] ) ) {
			$this->assertSame( $expected['error'], $result['error'] );
		}

		// Check hook fired status.
		$this->assertSame( $expected['hook_fired'] ?? false, $this->hook_fired );

		// Check hook data if hook was fired.
		if ( $expected['hook_fired'] ?? false ) {
			$this->assertSame( 'mcp-ai', $this->hook_data['source'] );
		}
	}

	/**
	 * Mock HTTP response for testing.
	 *
	 * @param mixed  $preempt Whether to preempt the request.
	 * @param array  $args    Request arguments.
	 * @param string $url     Request URL.
	 *
	 * @return array|false Mock response or false.
	 */
	public function mock_http_response( $preempt, $args, $url ) {
		return [
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
			'body'     => '<html><head><title>Test Page Title</title></head><body>Test content</body></html>',
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
