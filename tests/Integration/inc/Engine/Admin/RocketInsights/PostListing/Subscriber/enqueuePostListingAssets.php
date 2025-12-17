<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\PostListing\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use Brain\Monkey\Functions;
use ReflectionClass;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\PostListing\Subscriber::enqueue_post_listing_assets
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_EnqueuePostListingAssets extends AdminTestCase {
	/**
	 * Name of the transient used for storing remote settings data.
	 *
	 * @var string
	 */
	private $remote_settings_transient = 'wp_rocket_remote_settings';

	/**
	 * Remote settings response.
	 *
	 * @var array
	 */
	private $response;

	/**
	 * Set up test environment.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Enable Rocket Insights.
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		$this->setRoleCap( 'administrator', 'rocket_manage_options' );

		delete_transient( $this->remote_settings_transient );
		delete_transient( $this->remote_settings_transient . '_timeout' );
		delete_transient( $this->remote_settings_transient . '_timeout_active' );
	}

	/**
	 * Tear down test environment.
	 *
	 * @return void
	 */
	public function tear_down() {
		set_current_screen( 'front' );

		// Remove Rocket Insights filter.
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		$this->removeRoleCap( 'administrator', 'rocket_manage_options' );

		remove_filter( 'pre_http_request', [ $this, 'mock_remote_settings_response' ] );

		delete_transient( $this->remote_settings_transient );
		delete_transient( $this->remote_settings_transient . '_timeout' );
		delete_transient( $this->remote_settings_transient . '_timeout_active' );

		parent::tear_down();
	}

	/**
	 * Test if rocket-insights assets are enqueued on post listing pages.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result.
	 *
	 * @return void
	 */
	public function testShouldEnqueueAssetsOnPostListingPages( $config, $expected ) {
		$container = apply_filters( 'rocket_container', null ); // @phpstan-ignore-line
		$container->get( 'user' )->set_user( $config['customer_data'] );

		Functions\when( 'wp_parse_url' )->justReturn( $config['is_live_site'] );

		$this->setCurrentUser( 'administrator' );

		$this->response = $config['response'];
		add_filter( 'pre_http_request', [ $this, 'mock_remote_settings_response' ], 10, 3 );

		$remote_settings_data = $container->get( 'remote_settings_client' )->get_remote_settings_data();
		$remoteSettings = $container->get( 'remote_settings' );
    
		// Use reflection to mock private property.
		$reflection = new ReflectionClass( $remoteSettings );
		$property = $reflection->getProperty( 'remote_settings' );
		$property->setAccessible( true );
		$property->setValue( $remoteSettings, $remote_settings_data );

		// Reset scripts and styles.
		global $wp_scripts, $wp_styles;
		$wp_scripts = null;
		$wp_styles  = null;

		// Set the current screen.
		set_current_screen( $config['screen_id'] );
		$screen = get_current_screen();

		if ( ! empty( $config['post_type'] ) ) {
			$screen->post_type = $config['post_type'];
		}

		do_action( 'admin_enqueue_scripts' );

		$wp_scripts = wp_scripts();
		$wp_styles  = wp_styles();

		if ( $expected['should_enqueue'] ) {
			$this->assertArrayHasKey( 'rocket-insights', $wp_scripts->registered, 'rocket-insights JS should be registered' );
			$this->assertArrayHasKey( 'rocket-insights', $wp_styles->registered, 'rocket-insights CSS should be registered' );

			// Verify the script dependencies.
			$this->assertContains( 'jquery', $wp_scripts->registered['rocket-insights']->deps, 'rocket-insights JS should depend on jQuery' );
		} else {
			$this->assertArrayNotHasKey( 'rocket-insights', $wp_scripts->registered, 'rocket-insights JS should NOT be registered' );
			$this->assertArrayNotHasKey( 'rocket-insights', $wp_styles->registered, 'rocket-insights CSS should NOT be registered' );
		}
	}

	/**
	 * Mocks the HTTP response for remote settings requests to the plugin-settings.php endpoint.
	 *
	 * This method is intended to be used as a callback for the 'pre_http_request' filter in tests.
	 * It returns a mocked response if the request URL contains 'plugin-settings.php'.
	 *
	 * @param mixed  $preempt Whether to preempt the default HTTP request. Default false.
	 * @param array  $args    HTTP request arguments.
	 * @param string $url     The request URL.
	 *
	 * @return mixed Mocked response when URL matches, otherwise null.
	 */
	public function mock_remote_settings_response( $preempt, $args, $url ) {
		if ( false !== strpos( $url, 'plugin-settings.php' ) ) {
			return $this->response;
		}
	}
}
