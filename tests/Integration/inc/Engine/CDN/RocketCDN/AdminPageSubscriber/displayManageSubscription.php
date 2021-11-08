<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::display_manage_subscription
 * @uses   ::rocket_is_live_site
 * @uses   ::rocket_get_constant
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 *
 * @group  AdminOnly
 * @group  RocketCDN
 * @group  RocketCDNAdminPage
 */
class Test_DisplayManageSubscription extends TestCase {

	public function setUp() : void {
		parent::setUp();

		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	public function tearDown() {
		parent::tearDown();

		delete_transient( 'rocketcdn_status' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayExpected( $config, $expected ) {
		$this->white_label = isset( $config['white_label'] ) ? $config['white_label'] : $this->white_label;
		$this->home_url = $config['home_url'];

		if ( ! empty ( $expected ) ) {
			$expected = $this->format_the_html( $expected );
		}

		if ( ! empty( $config['rocketcdn_status'] ) ) {
			set_transient( 'rocketcdn_status', $config['rocketcdn_status'], MINUTE_IN_SECONDS );
		}

		ob_start();
		do_action( 'rocket_after_cdn_sections' );
		$actual = ob_get_clean();

		$this->assertSame( $expected, $this->format_the_html( $actual ) );
	}
}
