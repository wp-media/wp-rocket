<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Dreampress;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Dreampress;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Dreampress::set_varnish_host
 *
 * @group  Dreampress
 * @group  ThirdParty
 */
class Test_SetVarnishHost extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $hosts, $expected ) {
		$dreampress = new Dreampress();

		$this->assertSame(
			$expected,
			$dreampress->set_varnish_host( $hosts )
		);
	}
}
