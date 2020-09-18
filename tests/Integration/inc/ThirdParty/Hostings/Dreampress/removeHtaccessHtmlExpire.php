<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Dreampress;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Dreampress::remove_htaccess_html_expire
 *
 * @group  Dreampress
 * @group  ThirdParty
 */
class Test_RemoveHtaccessHtmlExpire extends TestCase {
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
	public function testShouldDoExpected( $rules, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_htaccess_mod_expires', $rules )
		);
	}

	public function set_home_url() {
		return 'https://wprocket.breakwpdh.com';
	}
}
