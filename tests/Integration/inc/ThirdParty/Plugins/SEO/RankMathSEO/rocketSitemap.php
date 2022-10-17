<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\SEO\RankMathSEO;

use RankMath\Sitemap\Router;
use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO::rocket_sitemap
 *
 * @group  RankMathSEO
 * @group  ThirdParty
 */
class Test_RocketSitemap extends TestCase
{
	use FilterTrait;

	public function setUp(): void
	{
		parent::setUp();
		$this->unregisterAllCallbacksExcept('rocket_sitemap_preload_list', 'rocket_sitemap', 15);
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
		Router::$sitemap = $config['sitemap'];
		$this->assertSame($expected, apply_filters('rocket_sitemap_preload_list', $config['sitemaps']));
	}

}
