<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\SEO\AllInOneSEOPack;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers  \WP_Rocket\ThirdParty\Plugins\SEO\AllInOneSEOPack::add_all_in_one_seo_sitemap
 * @group   AllInOneSeoPack
 */
class Test_AddAllInOneSeoSitemap extends TestCase
{
	use FilterTrait;

	protected $aioseop_options;

	public function setUp(): void
	{
		parent::setUp();
		add_filter('pre_option_aioseop_options', [$this, 'aioseop_options']);
		$this->unregisterAllCallbacksExcept('rocket_sitemap_preload_list', 'add_all_in_one_seo_sitemap', 15);
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

	public function aioseop_options() {
		return $this->aioseop_options;
	}
}
