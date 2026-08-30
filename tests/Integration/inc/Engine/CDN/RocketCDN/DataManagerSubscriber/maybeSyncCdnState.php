<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase as RocketCDNTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::maybe_sync_cdn_state
 *
 * Extends the RocketCDN TestCase (not AdminTestCase directly): it explicitly sets
 * set_current_screen( 'settings_page_wprocket' ) in set_up(), so is_admin() is
 * reliably true for the "should sync" cases instead of depending on ambient screen
 * state left over from whichever test ran previously.
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_MaybeSyncCdnState extends RocketCDNTestCase {

	/**
	 * DataManagerSubscriber instance.
	 *
	 * @var \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber
	 */
	private $subscriber;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$container        = apply_filters( 'rocket_container', null );
		$this->subscriber = $container->get( 'rocketcdn_data_manager_subscriber' );

		get_role( 'administrator' )->add_cap( 'rocket_manage_options' );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSyncCdnStateAsExpected( $config, $expected ) {
		$settings              = get_option( 'wp_rocket_settings', [] );
		$settings['cdn_state'] = $config['initial_cdn_state'];
		update_option( 'wp_rocket_settings', $settings );

		$result = $this->subscriber->maybe_sync_cdn_state( $config['transient_value'], 'rocketcdn_status' );

		$this->assertSame( $config['transient_value'], $result );

		$settings = get_option( 'wp_rocket_settings' );
		$this->assertSame( $expected['cdn_state'], $settings['cdn_state'] );
	}

	/**
	 * The is_admin() guard must be a genuine no-op on the front end: no write, no
	 * change to the return value, regardless of how conclusive the transient value is.
	 *
	 * @return void
	 */
	public function testShouldNotSyncOnFrontEnd() {
		set_current_screen( 'front' );

		$settings              = get_option( 'wp_rocket_settings', [] );
		$settings['cdn_state'] = 'nothing';
		update_option( 'wp_rocket_settings', $settings );

		$transient_value = [
			'plan_type'   => 'paid',
			'status_code' => 200,
		];

		$result = $this->subscriber->maybe_sync_cdn_state( $transient_value, 'rocketcdn_status' );

		$this->assertSame( $transient_value, $result );

		$settings = get_option( 'wp_rocket_settings' );
		$this->assertSame( 'nothing', $settings['cdn_state'] );
	}
}
