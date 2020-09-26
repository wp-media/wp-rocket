<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Dreampress;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Dreampress::set_varnish_host
 *
 * @group  Dreampress
 * @group  ThirdParty
 */
class Test_VarnishHost extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $hosts, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_varnish_ip', $hosts )
		);
	}
}
