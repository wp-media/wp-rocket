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

	protected $is_disabled;

	public function setUp(): void
	{
		parent::setUp();
		add_filter('pre_get_rocket_option_rank_math_xml_sitemap', [$this, 'is_disabled']);
		$this->unregisterAllCallbacksExcept('rocket_sitemap_preload_list', 'rocket_sitemap');
	}

	public function tearDown(): void
	{
		$this->restoreWpFilter('rocket_sitemap_preload_list');
		remove_filter('pre_get_rocket_option_rank_math_xml_sitemap', [$this, 'is_disabled']);
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Router::$sitemap = $config['sitemap'];
		$this->is_disabled = $config['is_disabled'];
		$this->assertSame($expected, apply_filters('rocket_sitemap_preload_list', $config['sitemaps']));
	}

	public function is_disabled() {
		return $this->is_disabled;
	}
}
