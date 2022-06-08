<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Jetpack;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Jetpack::add_jetpack_cookie_law_mandatory_cookie
 *
 * @group  Jetpack
 * @group  ThirdParty
 */
class Test_AddJetpackCookieLawMandatoryCookie extends TestCase
{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame( $expected, apply_filters( 'rocket_cache_mandatory_cookies', $config ));
	}
}
