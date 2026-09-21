<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::display_rocketcdn_status
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 * @uses   ::rocket_is_live_site
 * @uses   ::rocket_get_constant
 * @uses   \WP_Rocket\Abstract_Render::generate
 * @uses   ::rocket_direct_filesystem
 *
 * @group  AdminOnly
 * @group  RocketCDN
 * @group  RocketCDNAdminPage
 */
class Test_DisplayRocketcdnStatus extends TestCase {

	public static function set_up_before_class() {
		parent::set_up_before_class();

		update_option( 'date_format', 'Y-m-d' );
	}

	public function set_up() : void {
		parent::set_up();

		add_filter( 'home_url', [ $this, 'home_url_cb' ] );

		// AdminPageSubscriber is only wired onto rocket_dashboard_after_account_data at
		// bootstrap when is_admin() was true at Plugin::__construct() time -- evaluated
		// once, before this (or any) test's set_current_screen() call can influence it.
		// Register it explicitly here so unregisterAllCallbacksExcept() always has a
		// matching callback to isolate, regardless of that one-time bootstrap timing.
		$container  = apply_filters( 'rocket_container', null );
		$subscriber = $container->get( 'rocketcdn_admin_subscriber' );
		add_action( 'rocket_dashboard_after_account_data', [ $subscriber, 'display_rocketcdn_status' ] );

		$this->unregisterAllCallbacksExcept( 'rocket_dashboard_after_account_data', 'display_rocketcdn_status' );
	}

	public function tear_down() {
		delete_transient( 'rocketcdn_status' );

		$this->restoreWpHook( 'rocket_dashboard_after_account_data' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayExpected( $rocketcdn_status, $expected, $config ) {
		$this->white_label = isset( $config['white_label'] ) ? $config['white_label'] : $this->white_label;
		$this->home_url    = $config['home_url'];
		set_transient( 'rocketcdn_status', $rocketcdn_status, MINUTE_IN_SECONDS );

		$container = apply_filters( 'rocket_container', null );
		$user      = $container->get( 'user' );
		$user->set_user( (object) [ 'is_reseller' => $config['is_reseller'] ?? false ] );

		ob_start();
		do_action( 'rocket_dashboard_after_account_data' );
		$actual = ob_get_clean();

		$this->assertSame(
			$this->format_the_html( $expected['integration'] ),
			$this->format_the_html( $actual )
		);
	}
}
