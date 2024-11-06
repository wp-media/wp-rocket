<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\RankMathSEO;

use RankMath\Sitemap\Router;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO::add_sitemap
 *
 * @group RankMathSEO
 * @group ThirdParty
 */
class Test_RocketSitemap extends TestCase {
	protected $option;
	protected $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->subscriber = new RankMathSEO();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		Router::$sitemap = $config['sitemap'];

		$this->assertSame(
			$expected,
			$this->subscriber->add_sitemap( $config['sitemaps'] )
		);
	}
}
