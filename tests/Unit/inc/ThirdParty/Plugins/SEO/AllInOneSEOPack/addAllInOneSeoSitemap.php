<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\AllInOneSEOPack;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\AllInOneSEOPack;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SEO\AllInOneSEOPack::add_all_in_one_seo_sitemap
 *
 * @group  AllInOneSEOPack
 * @group  ThirdParty
 */
class Test_AddAllInOneSeoSitemap extends TestCase
{
	protected $option;
	protected $subscriber;
	protected $aioseo;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		if(! defined('AIOSEOP_VERSION')) {
			define('AIOSEOP_VERSION', true);
		}
		if(! defined('AIOSEO_VERSION')) {
			define('AIOSEO_VERSION', true);
		}
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->option = Mockery::mock(Options_Data::class);
		$this->subscriber = new AllInOneSEOPack($this->option);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->configureCheckVersion($config);
		$this->configureCheckOption($config);
		$this->configureCreateSitemap($config);
		$this->assertSame($expected, $this->subscriber->add_all_in_one_seo_sitemap($config['sitemaps']));
	}

	protected function configureCheckVersion($config) {
		if($config['version'] == 4) {

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
	}

	protected function configureCheckOption($config) {
		if($config['version'] !== 3) {
			return;
		}

		Functions\expect('get_option')->with('aioseop_options')->andReturn($config['options']);
	}

	protected function configureCreateSitemap($config) {
		if(! $config['aioseo_enabled']) {
			return;
		}

		Functions\when('trailingslashit')->returnArg();
		Functions\when('home_url')->justReturn($config['home_url']);

		if($config['version'] === 3) {
			Filters\expectApplied('aiosp_sitemap_filename')->with('sitemap')->andReturn($config['sitemap']);
		} else {
			Filters\expectApplied('aioseo_sitemap_filename')->with('sitemap')->andReturn($config['sitemap']);
		}
	}
}
