<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\SEO\TheSEOFramework;

use The_SEO_Framework\Bridges\Sitemap;
use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\SEO\TheSEOFramework::add_tsf_sitemap_to_preload
 *
 * @group  TheSEOFramework
 * @group  ThirdParty
 */
class Test_AddTsfSitemapToPreload extends TestCase
{
	use FilterTrait;

	protected $is_disabled;

	public function setUp(): void
	{
		parent::setUp();
		add_filter('pre_get_rocket_option_tsf_xml_sitemap', [$this, 'is_disabled']);
		$this->unregisterAllCallbacksExcept('rocket_sitemap_preload_list', 'add_tsf_sitemap_to_preload');
	}

	public function tearDown(): void
	{
		$this->restoreWpFilter('rocket_sitemap_preload_list');
		remove_filter('pre_get_rocket_option_tsf_xml_sitemap', [$this, 'is_disabled']);
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->is_disabled = $config['is_tsf_active'];
		Functions\when('rocket_get_constant')->justReturn($config['version']);
		Sitemap::$endpoints = $config['endpoints'];
		Sitemap::$url = $config['url'];
		Sitemap::$sitemap = $config['sitemap'];
		$this->assertSame($expected, apply_filters('rocket_sitemap_preload_list', $config['sitemaps']));
	}

	public function is_disabled() {
		return $this->is_disabled;
	}
}
