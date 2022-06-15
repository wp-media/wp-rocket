<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\RankMathSEO;

use Mockery;
use RankMath\Sitemap\Router;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO::rocket_sitemap
 *
 * @group  AllInOneSEOPack
 * @group  ThirdParty
 */
class Test_RocketSitemap extends TestCase
{
	protected $option;
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->option = Mockery::mock(Options_Data::class);
		$this->subscriber = new RankMathSEO($this->option);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Router::$sitemap = $config['sitemap'];
		$this->assertSame($expected, $this->subscriber->rocket_sitemap($config['sitemaps']));
	}
}
