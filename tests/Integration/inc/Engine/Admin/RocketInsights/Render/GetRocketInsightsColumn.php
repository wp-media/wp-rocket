<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Render;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Render::get_rocket_insights_column
 *
 * Tests the new functionality where the "Test the page" button is enabled/disabled based on
 * whether the user can add more pages (wpr_can_add_pages) or has credit (wpr_has_credit).
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_GetRocketInsightsColumn extends TestCase {
	use DBTrait;

	protected static $container;
	private $render;
	private $query;
	private $user;

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

		// Get container and dependencies
		self::$container = apply_filters( 'rocket_container', null );
		$this->render    = self::$container->get( 'ri_render' );
		$this->query     = self::$container->get( 'ri_query' );
		$this->user      = self::$container->get( 'user' );
	}

	public function tear_down() {
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		// Clean up credit option
		delete_option( 'wp_rocket_pm_credit' );

		// Remove Performance Monitoring enabled filter
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		// Set up user data for the test
		$this->user->set_user( $config['customer_data']->generate() );

		// Set credit if specified
		if ( isset( $config['credit'] ) ) {
			update_option( 'wp_rocket_pm_credit', $config['credit'] );
		}

		// Add existing URLs if specified
		if ( isset( $config['existing_urls'] ) ) {
			foreach ( $config['existing_urls'] as $url_data ) {
				$this->addPerformanceMonitoring( $url_data );
			}
		}
		$post_id = 1;

		if ( isset( $config['post_status'] ) ) {
			$post_id = $this->factory->post->create( [
				'post_title' => 'Test Post',
				'post_content' => 'Content',
				'post_status' => 'draft',
				'post_type' => 'post',
				'post_name' => 'page-to-test',
			] );
		}

		// Generate the HTML for the column
		$html = $this->render->get_rocket_insights_column( $config['url'], $post_id );

		// Check for button state (enabled/disabled)
		if ( isset( $expected['button_enabled'] ) ) {
			$this->assertStringContainsString( 'type="button"', $html );
			if ( $expected['button_enabled'] ) {
				// Button should be clickable (not have wpr-ri-no-credit class)
				$this->assertStringContainsString( 'wpr-ri-test-page', $html );
				$this->assertStringNotContainsString( 'wpr-ri-no-credit', $html );
				$this->assertStringContainsString( 'class="wpr-ri-test-page "', $html );
			} else {
				// Button should be disabled (has wpr-ri-no-credit class)
				$this->assertStringContainsString( 'wpr-ri-test-page wpr-ri-no-credit', $html );
				$this->assertStringContainsString( 'class="wpr-ri-test-page wpr-ri-no-credit"', $html );
			}
		}

		// Check for "Test the page" text
		if ( isset( $expected['contains'] ) ) {
			foreach ( $expected['contains'] as $string ) {
				$this->assertStringContainsString( $string, $html );
			}
		}

		// Check for strings that should not be present
		if ( isset( $expected['not_contains'] ) ) {
			foreach ( $expected['not_contains'] as $string ) {
				$this->assertStringNotContainsString( $string, $html );
			}
		}

		// Verify URL is present in the output
		$this->assertStringContainsString( $config['url'], $html );
	}
}
