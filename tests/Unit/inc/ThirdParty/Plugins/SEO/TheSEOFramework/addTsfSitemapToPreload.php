<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\TheSEOFramework;

use Mockery;
use The_SEO_Framework\Bridges\Sitemap;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\TheSEOFramework;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SEO\TheSEOFramework::add_tsf_sitemap_to_preload
 *
 * @group  TheSEOFramework
 * @group  ThirdParty
 */
class Test_AddTsfSitemapToPreload extends TestCase
{
	protected $option;

	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->option = Mockery::mock(Options_Data::class);
		$this->subscriber = new TheSEOFramework($this->option);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->configureCheckVersion($config);
		$this->configureInferior4($config);
		$this->configure4($config);
		$this->assertSame($expected, $this->subscriber->add_tsf_sitemap_to_preload($config['sitemaps']));
	}

	protected function configureCheckVersion($config) {
		Functions\when('rocket_get_constant')->justReturn($config['version']);
	}

	protected function configureInferior4($config) {
		if($config['version'] == '4.0') {
			return;
		}
		Sitemap::$sitemap = $config['sitemap'];
	}

	protected function configure4($config) {
		if($config['version'] != '4.0') {
			return;
		}
		Sitemap::$endpoints = $config['endpoints'];
		Sitemap::$url = $config['url'];
	}
}
