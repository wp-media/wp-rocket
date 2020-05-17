<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::add_subscription_modal
 * @uses   ::rocket_is_live_site
 * @uses   ::rocket_get_constant
 *
 * @group  RocketCDN
 * @group  AdminOnly
 * @group  RocketCDNAdminPage
 */
class Test_AddSubscriptionModal extends TestCase {

	public function setUp() {
		parent::setUp();

		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayExpected( $home_url, $expected ) {
		$this->home_url = $home_url;

		ob_start();
		do_action( 'rocket_settings_page_footer' );
		$actual = ob_get_clean();

		if ( ! empty ( $expected ) ) {
			$expected = $this->format_the_html( $expected );
		}

		if ( ! empty ( $actual ) ) {
			$actual = $this->format_the_html( $actual );
		}

		$this->assertSame( $expected, $actual );
	}
}
