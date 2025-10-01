<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::render_global_score_widget
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class RenderGlobalScoreWidgetTest extends TestCase {
	use DBTrait;

	protected static $container;

	private $performance_monitoring;

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

		// Get container
		self::$container = apply_filters( 'rocket_container', null );

		// Clear global score cache
		$global_score = self::$container->get( 'pm_global_score' );
		$global_score->reset();
	}

	public function tear_down() {
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		// Remove Performance Monitoring enabled filter
		remove_filter( 'rocket_performance_monitoring_enabled', '__return_true' );

		remove_filter( 'pre_get_rocket_option_performance_monitoring', [ $this, 'set_performance_monitoring' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->setUpTest( $config );

		// Capture output
		ob_start();
		do_action( 'rocket_dashboard_sidebar' );
		$output = ob_get_clean();

		$this->assertOutput( $output, $expected );
	}

	private function setUpTest( $config ) {
		$this->performance_monitoring = $config['performance_monitoring'];
		add_filter( 'pre_get_rocket_option_performance_monitoring', [ $this, 'set_performance_monitoring' ] );

		// Set up user data if provided
		if ( isset( $config['customer_data'] ) ) {
			$user = self::$container->get( 'user' );
			$user->set_user( $config['customer_data']->generate() );
		}

		// Set up database rows if provided
		if ( isset( $config['rows'] ) ) {
			foreach ( $config['rows'] as $row ) {
				$this->addRowToDatabase( $row );
			}
		}
	}

	private function addRowToDatabase( $row_data ) {
		$defaults = [
			'url' => 'http://example.org',
			'title' => 'Test Page',
			'is_mobile' => 0,
			'job_id' => 'test_' . uniqid(),
			'queue_name' => 'rocket-performance-monitoring',
			'retries' => 1,
			'status' => 'pending',
			'data' => '{}',
			'error_message' => '',
			'submitted_at' => gmdate( 'Y-m-d H:i:s' ),
			'last_accessed' => gmdate( 'Y-m-d H:i:s' ),
			'modified' => gmdate( 'Y-m-d H:i:s' ),
			'next_retry_time' => gmdate( 'Y-m-d H:i:s' ),
		];

		$row_data = array_merge( $defaults, $row_data );

		// Convert is_mobile from boolean to int if needed
		if ( isset( $row_data['is_mobile'] ) && is_bool( $row_data['is_mobile'] ) ) {
			$row_data['is_mobile'] = $row_data['is_mobile'] ? 1 : 0;
		}

		$this->addPerformanceMonitoring( $row_data );
	}

	private function assertOutput( $output, $expected ) {
		// If expected HTML is provided, compare directly with trimming
		if ( isset( $expected['html'] ) ) {
			$this->assertSame(
				$this->normalizeHtml( $expected['html'] ),
				$this->normalizeHtml( $output )
			);
		}

		// Check for specific elements
		if ( isset( $expected['contains'] ) ) {
			foreach ( $expected['contains'] as $string ) {
				$this->assertStringContainsString( $string, $output );
			}
		}

		if ( isset( $expected['not_contains'] ) ) {
			foreach ( $expected['not_contains'] as $string ) {
				$this->assertStringNotContainsString( $string, $output );
			}
		}

		// Check button state
		if ( isset( $expected['button_enabled'] ) ) {
			if ( $expected['button_enabled'] ) {
				$this->assertStringNotContainsString( 'disabled', $output );
				$this->assertStringNotContainsString( 'wpr-btn-with-tool-tip', $output );
			} else {
				$this->assertStringContainsString( 'disabled', $output );
				$this->assertStringContainsString( 'wpr-btn-with-tool-tip', $output );
				$this->assertStringContainsString( 'Maximum number of URLs reached', $output );
			}
		}
	}

	/**
	 * Normalize HTML by removing extra whitespace and formatting consistently
	 */
	private function normalizeHtml( $html ) {
		// Remove all whitespace between tags
		$html = preg_replace('/>\s+</', '><', trim($html));

		// Normalize whitespace within text nodes, but preserve single spaces
		$html = preg_replace('/\s+/', ' ', $html);

		// Trim text content around tags while preserving structure
		$html = preg_replace('/>\s+([^<]+?)\s+</', '>$1<', $html);

		// Handle dynamic content like nonces - replace with placeholder
		$html = preg_replace('/&_wpnonce=[a-f0-9]+/', '&_wpnonce=DYNAMIC_NONCE', $html);

		return trim($html);
	}

	private function getExpectedHtml( $filename ) {
		$file_path = dirname( __FILE__ ) . '/../../../../../../Fixtures/inc/Engine/Admin/RocketInsights/Subscriber/html/' . $filename;

		if ( ! file_exists( $file_path ) ) {
			$this->fail( "HTML fixture file not found: {$file_path}" );
		}

		return file_get_contents( $file_path );
	}

	public function set_performance_monitoring() {
        return $this->performance_monitoring;
    }
}
