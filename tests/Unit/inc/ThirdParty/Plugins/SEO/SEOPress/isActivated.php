<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\SEOPress;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\SEOPress;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SEO\SEOPress::is_activated
 *
 * Uses the same namespaced function_exists() override pattern as
 * PWA/excludeServiceWorker.php: the class_exists() narrowing-extension
 * conflict documented for PDFEmbedder/Weglot/Jetpack is specific to
 * class_exists(), not function_exists(), so `composer run-stan` stays clean
 * with this override declared for WP_Rocket\ThirdParty\Plugins\SEO. Note:
 * is_activated() extracts only the presence sentinel — the untouched
 * get_subscribed_events() compound guard (xml-sitemap toggle +
 * SitemapOption->isEnabled()) stays untested here since it isn't reachable
 * from is_activated().
 *
 * @group SEOPress
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	public static $function_exists = false;

	protected function tearDown(): void {
		self::$function_exists = false;

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		self::$function_exists = $config['function_exists'];

		$this->assertSame( $expected, SEOPress::is_activated() );
	}
}

namespace WP_Rocket\ThirdParty\Plugins\SEO;

function function_exists( $function ) {
	if ( $function === 'seopress_get_toggle_option' ) {
		return \WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\SEOPress\Test_IsActivated::$function_exists;
	}

	return \function_exists( $function );
}
