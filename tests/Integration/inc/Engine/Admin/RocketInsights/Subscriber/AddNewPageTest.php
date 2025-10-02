<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::add_new_page
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class AddNewPageTest extends AjaxTestCase {
	use DBTrait;

	private $hook_fired = false;
	private $container;

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

		remove_all_actions( 'wp_rocket_first_install' );

		// Clean up data before each test
		self::truncatePerformanceMonitoringTable();

		// Enable Performance Monitoring for the test
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Set the AJAX action
		$this->action = 'rocket_rocket_insights_add_new_page';

		// Add a hook to capture when rocket_rocket_insights_job_added is fired
		add_action( 'rocket_rocket_insights_job_added', [ $this, 'capture_hook_fired' ] );

		$this->hook_fired = false;

		$this->container = apply_filters('rocket_container', null);
	}

	public function tear_down() {
		// Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		// Remove Performance Monitoring enabled filter
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		// Remove URL limit filter if set
		remove_filter( 'rocket_rocket_insights_allow_add_page', '__return_false' );

		// Remove our test hook
		remove_action( 'rocket_rocket_insights_job_added', [ $this, 'capture_hook_fired' ] );

		// Remove mock HTTP filter
		remove_filter( 'pre_http_request', [ $this, 'mock_http_request' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->setUpTest( $config );

		$this->executeAjaxCall();

		$this->assertResponse( $expected );

		$this->cleanUpTest( $config );
	}

	private function setUpTest( $config ) {

		foreach ( $config['rows'] as $row ) {
			$this->addPerformanceMonitoring( $row );
		}

		$this->container->get('user')->set_user($config['customer_data']->generate());

		// Set up the nonce
		$_POST['nonce'] = \wp_create_nonce( 'rocket-ajax' );

		// Set up POST data if provided
		if ( isset( $config['post_data'] ) ) {
			foreach ( $config['post_data'] as $key => $value ) {
				$_POST[ $key ] = $value;
			}
		}

		// Mock HTTP requests if needed for URL validation
		if ( isset( $config['mock_http'] ) && $config['mock_http'] ) {
			add_filter( 'pre_http_request', [ $this, 'mock_http_request' ], 10, 3 );
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

		// Check if database entry was created
		if ( isset( $expected['database_entries'] ) && $expected['database_entries'] > 0 ) {
			$items = $this->container->get( 'ri_query' )->query( [] );
			$this->assertCount( $items, $expected['database_entries'] );
		}

		// Check if hook was fired
		if ( isset( $expected['hook_fired'] ) && $expected['hook_fired'] ) {
			$this->assertTrue( $this->hook_fired );
		}

		// Check response data if provided
		if ( isset( $expected['response_data'] ) ) {
			foreach ( $expected['response_data'] as $key => $value ) {
				$this->assertArrayHasKey( $key, $response['data'] );

				// For specific fields, check the expected values
				if ( $key === 'can_add_pages' && $value !== null ) {
					$this->assertSame( $value, $response['data'][$key] );
				}
			}
		}
	}

	private function assertErrorResponse( $response, $expected ) {
		$this->assertFalse( $response['success'] );

		// Check error message if provided
		if ( isset( $expected['error_message'] ) ) {
			$this->assertStringContainsString( $expected['error_message'], $response['data']['message'] );
		}
	}

	private function cleanUpTest( $config ) {
		// Clean up filters
		if ( isset( $config['filters'] ) ) {
			foreach ( $config['filters'] as $filter => $callback ) {
				remove_filter( $filter, $callback );
			}
		}
	}

	/**
	 * Callback to capture when rocket_rocket_insights_job_added hook is fired.
	 *
	 * @param string $url The URL that was added for monitoring.
	 */
	public function capture_hook_fired( $url ) {
		$this->hook_fired = true;
	}

	/**
	 * Mock HTTP requests for URL validation.
	 *
	 * @param false|array|\WP_Error $preempt A preemptive return value of an HTTP request.
	 * @param array                $args HTTP request arguments.
	 * @param string               $url The request URL.
	 * @return array|false
	 */
	public function mock_http_request( $preempt, $args, $url ) {
		// Mock successful response for URLs on the test domain (example.org)
		if ( strpos( $url, 'http://example.org' ) === 0 ) {
			return [
				'response' => [
					'code' => 200,
				],
				'body' => '<html><head><title>Test Page Title</title></head><body>Test content</body></html>',
			];
		}

		// Mock successful response for external URLs (use a local test URL instead of Google)
		if ( strpos( $url, 'http://example.org/test-external' ) === 0 ) {
			return [
				'response' => [
					'code' => 200,
				],
				'body' => '<html><head><title>External Test Page</title></head><body>External test content</body></html>',
			];
		}

		// Mock successful response for example.org URL used in tests
		if ( strpos( $url, 'https://example.org' ) === 0 ) {
			return [
				'response' => [
					'code' => 200,
				],
				'body' => '<html><head><title>Example Domain</title></head><body>Example domain content</body></html>',
			];
		}

		// Mock 404 for invalid URLs
		return [
			'response' => [
				'code' => 404,
			],
			'body' => 'Not found',
		];
	}
}
