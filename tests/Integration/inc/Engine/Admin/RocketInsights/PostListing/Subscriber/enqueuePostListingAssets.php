<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\PostListing\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\PostListing\Subscriber::enqueue_post_listing_assets
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_EnqueuePostListingAssets extends AdminTestCase {
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

		// Reset scripts and styles.
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		global $wp_scripts, $wp_styles;
		$wp_scripts = null;
		$wp_styles  = null;
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

		// Set the current screen.
		set_current_screen( $config['screen_id'] );
		$screen = get_current_screen();

		if ( ! empty( $config['post_type'] ) ) {
			$screen->post_type = $config['post_type'];
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		do_action( 'admin_enqueue_scripts' );

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_scripts = wp_scripts();
		$wp_styles  = wp_styles();
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

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
}
