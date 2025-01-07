<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Jetpack;

use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Jetpack::add_jetpack_sitemap
 *
 * @group  Jetpack
 * @group  ThirdParty
 */
class Test_AddJetpackSitemap extends TestCase
{
	use IsolateHookTrait;

	protected $jetpack_xml_sitemap;

	public function setUp(): void
	{
		parent::setUp();
		$this->unregisterAllCallbacksExcept('rocket_sitemap_preload_list', 'add_jetpack_sitemap');
	}

	public function tearDown(): void
	{
		$this->restoreWpHook('rocket_first_install_options');
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame( $expected, apply_filters( 'rocket_sitemap_preload_list', $config['sitemaps'] ));
	}

}
