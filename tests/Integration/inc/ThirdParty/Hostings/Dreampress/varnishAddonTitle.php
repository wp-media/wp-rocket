<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Dreampress;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Dreampress::varnish_addon_title
 *
 * @group  Dreampress
 * @group  ThirdParty
 */
class Test_VarnishAddonTitle extends TestCase {
	public function setUp() {
		parent::setUp();

		add_filter( 'home_url', [ $this, 'set_home_url' ] );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'home_url', [ $this, 'set_home_url' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_varnish_field_settings', $settings )
		);
	}

	public function set_home_url() {
		return 'https://wprocket.breakwpdh.com';
	}
}
