<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Jetpack;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Jetpack::jetpack_sitemap_option_sanitize
 *
 * @group  Jetpack
 * @group  ThirdParty
 */
class Test_JetpackSitemapOptionSanitize extends TestCase
{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame( $expected, apply_filters( 'rocket_inputs_sanitize', $config ));
	}
}
