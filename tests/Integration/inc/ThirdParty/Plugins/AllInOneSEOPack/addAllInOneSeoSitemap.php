<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\AllInOneSEOPack;

use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers  \WP_Rocket\ThirdParty\Plugins\AllInOneSEOPack::add_all_in_one_seo_sitemap
 * @group   AllInOneSeoPack
 */
class Test_AddAllInOneSeoSitemap extends TestCase
{
	use FilterTrait;

	protected $all_in_one_seo_xml_sitemap;

	protected $aioseop_options;

	public function setUp(): void
	{
		parent::setUp();
		add_filter('pre_get_rocket_option_all_in_one_seo_xml_sitemap', [$this, 'all_in_one_seo_xml_sitemap']);
		add_filter('pre_option_aioseop_options', [$this, 'aioseop_options']);
		$this->unregisterAllCallbacksExcept('rocket_sitemap_preload_list', 'add_all_in_one_seo_sitemap');
	}

	public function tearDown(): void
	{
		$this->restoreWpFilter('rocket_sitemap_preload_list');
		remove_filter('pre_option_aioseop_options', [$this, 'aioseop_options']);
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->all_in_one_seo_xml_sitemap = $config['is_enabled'];
		$this->aioseop_options = $config['options'];
		if($config['version'] === 4) {
			$this->aioseo = (object) [
				'options' => (object) [
					'sitemap' => (object) [
						'general' => (object) [
							'enable' => $config['aioseo_enabled']
						]
					]
				]
			];
			Functions\when('aioseo')->justReturn($this->aioseo);
		}
		$this->assertSame($expected, apply_filters('rocket_sitemap_preload_list', $config['sitemaps']));
	}

	public function all_in_one_seo_xml_sitemap() {
		return $this->all_in_one_seo_xml_sitemap;
	}

	public function aioseop_options() {
		return $this->aioseop_options;
	}
}
