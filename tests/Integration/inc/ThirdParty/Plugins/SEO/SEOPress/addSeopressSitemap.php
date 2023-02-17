<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\SEO\SEOPress;

use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\SEO\SEOPress::add_seopress_sitemap
 *
 * @group  SEOPress
 * @group  ThirdParty
 */
class Test_AddSeopressSitemap extends TestCase
{
	use FilterTrait;


	public function setUp(): void
	{
		parent::setUp();
		$this->unregisterAllCallbacksExcept('rocket_sitemap_preload_list', 'add_seopress_sitemap', 15);
	}

	public function tearDown(): void
	{
		$this->restoreWpFilter('rocket_sitemap_preload_list');
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->assertSame($expected, apply_filters('rocket_sitemap_preload_list', $config['sitemaps']));
	}

}
