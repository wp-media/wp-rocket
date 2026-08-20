<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::maybe_sync_cdn_state
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_MaybeSyncCdnState extends AdminTestCase {

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
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSyncCdnStateAsExpected( $config, $expected ) {
		$settings                = get_option( 'wp_rocket_settings', [] );
		$settings['cdn_state']   = $config['initial_cdn_state'];
		update_option( 'wp_rocket_settings', $settings );

		$result = $this->subscriber->maybe_sync_cdn_state( $config['transient_value'], 'rocketcdn_status' );

		$this->assertSame( $config['transient_value'], $result );

		$settings = get_option( 'wp_rocket_settings' );
		$this->assertSame( $expected['cdn_state'], $settings['cdn_state'] );
	}
}
