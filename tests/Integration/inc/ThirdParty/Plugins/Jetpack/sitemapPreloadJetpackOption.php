<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Jetpack;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Jetpack::sitemap_preload_jetpack_option
 *
 * @group  Jetpack
 * @group  ThirdParty
 */
class Test_SitemapPreloadJetpackOption extends TestCase
{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame( $expected, apply_filters( 'rocket_sitemap_preload_options', $config ));
	}
}
