<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Jetpack;

use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Plugins\Jetpack;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Jetpack::add_jetpack_sitemap_option
 *
 * @group  Jetpack
 * @group  ThirdParty
 */
class Test_AddJetpackSitemapOption extends TestCase
{
	use FilterTrait;

	public function setUp(): void
	{
		parent::setUp();
		$this->unregisterAllCallbacksExcept('rocket_first_install_options', 'add_jetpack_sitemap_option');
	}

	public function tear_down(): void
	{
		$this->restoreWpFilter('rocket_first_install_options');
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame( $expected, apply_filters( 'rocket_first_install_options', $config ));
	}
}
