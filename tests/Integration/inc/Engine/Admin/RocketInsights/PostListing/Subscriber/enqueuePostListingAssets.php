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
	private $transient;

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

		remove_filter( 'pre_transient_wp_rocket_remote_settings', [ $this, 'mock_transient' ] );

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
		$this->rocket_version = '3.20.3';
		$container = apply_filters( 'rocket_container', null ); // @phpstan-ignore-line
		$container->get( 'user' )->set_user( $config['customer_data'] );

		Functions\when( 'wp_parse_url' )->justReturn( $config['is_live_site'] );

		$this->setCurrentUser( 'administrator' );

		$this->transient = $config['transient'];
		add_filter( 'pre_transient_wp_rocket_remote_settings', [ $this, 'mock_transient' ] );

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
	 * Mock the transient value.
	 *
	 * @return mixed Mocked response when URL matches, otherwise null.
	 */
	public function mock_transient( ) {
		return $this->transient;
	}
}
