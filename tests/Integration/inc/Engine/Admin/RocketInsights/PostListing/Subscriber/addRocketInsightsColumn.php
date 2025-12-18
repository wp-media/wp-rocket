<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\PostListing\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use Brain\Monkey\Functions;
use ReflectionClass;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\PostListing\Subscriber::add_rocket_insights_column
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_AddRocketInsightsColumn extends AdminTestCase {
	/**
	 * Container instance.
	 */
	private $container;

	/**
	 * Subscriber instance.
	 *
	 * @var \WP_Rocket\Engine\Admin\RocketInsights\PostListing\Subscriber
	 */
	private $subscriber;
	
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
	 * Setup before tests.
	 */
	public function set_up() {
		parent::set_up();
		
		// Get the subscriber instance from container using the filter.
		$this->container = apply_filters( 'rocket_container', null );
		$this->subscriber = $this->container->get( 'ri_post_listing_subscriber' );

		// Enable Rocket Insights.
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		delete_transient( $this->remote_settings_transient );
		delete_transient( $this->remote_settings_transient . '_timeout' );
		delete_transient( $this->remote_settings_transient . '_timeout_active' );
	}

	public function tear_down() {
		// Remove Rocket Insights filter.
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );
		remove_filter( 'pre_http_request', [ $this, 'mock_remote_settings_response' ] );

		delete_transient( $this->remote_settings_transient );
		delete_transient( $this->remote_settings_transient . '_timeout' );
		delete_transient( $this->remote_settings_transient . '_timeout_active' );

		parent::tear_down();
	}
	
	/**
	 * Test if Rocket Insights column is added to post listing columns.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result.
	 *
	 * @return void
	 */
	public function testShouldAddRocketInsightsColumn( $config, $expected ) {
		$this->container->get( 'user' )->set_user( $config['customer_data'] );
		Functions\when( 'wp_parse_url' )->justReturn( $config['is_live_site'] );

		$this->response = $config['response'];
		add_filter( 'pre_http_request', [ $this, 'mock_remote_settings_response' ], 10, 3 );

		$remote_settings_data = $this->container->get( 'remote_settings_client' )->get_remote_settings_data();
		$remoteSettings = $this->container->get( 'remote_settings' );
    
		// Use reflection to mock private property.
		$reflection = new ReflectionClass( $remoteSettings );
		$property = $reflection->getProperty( 'remote_settings' );
		$property->setAccessible( true );
		$property->setValue( $remoteSettings, $remote_settings_data );

		// Call the method directly instead of relying on filter subscription.
		$columns = $this->subscriber->add_rocket_insights_column( $config['columns'] );

		if ( '' !== $expected['column_label'] ) {
			$this->assertArrayHasKey( 'rocket_insights', $columns, 'Rocket Insights column should be present' );
			$this->assertSame( $expected['column_label'], $columns['rocket_insights'], 'Column label should match' );

			return;
		}

		$this->assertArrayNotHasKey( 'rocket_insights', $columns, 'Rocket Insights column should not be present' );
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

		return $preempt;
	}
}
