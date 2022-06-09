<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\SEOPress;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\SEOPress;
use Brain\Monkey\Functions;

class Test_AddSeopressSitemap extends TestCase
{
	protected $option;

	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->option = Mockery::mock(Options_Data::class);
		$this->subscriber = new SEOPress($this->option);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->option->expects()->get('seopress_xml_sitemap', false)->andReturn($config['is_enabled']);
		$this->configureAddSitemap($config);
		$this->assertSame($expected, $this->subscriber->add_seopress_sitemap($config['sitemaps']));
	}

	protected function configureAddSitemap($config) {
		if(! $config['is_enabled']) {
			return;
		}

		Functions\expect('get_home_url')->andReturn($config['home_url']);
	}
}
