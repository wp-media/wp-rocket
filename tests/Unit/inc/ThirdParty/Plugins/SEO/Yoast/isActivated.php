<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\Yoast;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\Yoast;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SEO\Yoast::is_activated
 *
 * @group Yoast
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests Yoast::is_activated() against the presence/absence of WPSEO_VERSION.
	 *
	 * Note: is_activated() extracts only the presence sub-expression from the
	 * untouched private is_sitemap_enabled() (which also checks the
	 * wpseo_xml/enablexmlsitemap option) — that business-logic branch stays
	 * untested here since it isn't reachable from is_activated().
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['wpseo_version'] ) {
			$this->constants['WPSEO_VERSION'] = $config['wpseo_version'];
		}

		$this->assertSame( $expected, Yoast::is_activated() );
	}
}
