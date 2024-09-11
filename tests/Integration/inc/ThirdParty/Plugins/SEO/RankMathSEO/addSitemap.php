<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\SEO\RankMathSEO;

use RankMath\Sitemap\Router;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO::add_sitemap
 *
 * @group RankMathSEO
 * @group ThirdParty
 */
class TestRocketSitemap extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_sitemap_preload_list', 'add_sitemap', 15 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_sitemap_preload_list' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Router::$sitemap = $config['sitemap'];

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_sitemap_preload_list', $config['sitemaps'] )
		);
	}
}
