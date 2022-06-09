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

	protected $is_disabled;

	public function setUp(): void
	{
		parent::setUp();
		add_filter('pre_get_rocket_option_seopress_xml_sitemap', [$this, 'is_disabled']);
		$this->unregisterAllCallbacksExcept('rocket_sitemap_preload_list', 'add_seopress_sitemap');
	}

	public function tearDown(): void
	{
		$this->restoreWpFilter('rocket_sitemap_preload_list');
		remove_filter('pre_get_rocket_option_seopress_xml_sitemap', [$this, 'is_disabled']);
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->is_disabled = $config['is_enabled'];
		$this->assertSame($expected, apply_filters('rocket_sitemap_preload_list', $config['sitemaps']));
	}

	public function is_disabled() {
		return $this->is_disabled;
	}
}
