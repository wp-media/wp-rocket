<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\TheSEOFramework;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\TheSEOFramework;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SEO\TheSEOFramework::is_activated
 *
 * tests/Unit/bootstrap.php unconditionally preloads
 * tests/Fixtures/inc/ThirdParty/Plugins/SEO/TheSEOFramework/fixtures.php for
 * every Unit test (needed by the pre-existing addTsfSitemapToPreload.php
 * callback test), which defines a real global the_seo_framework() function
 * returning a Sitemap stub whose $loaded property defaults to the truthy
 * string 'loaded'. That preload runs again in an @runInSeparateProcess fork
 * too (the bootstrap re-executes for every isolated process), so eval()-
 * redefining the_seo_framework() there would fatally collide with the
 * already-declared one. Exactly like the pre-existing
 * TranslatePress::is_activated() precedent (I18n/TranslatePress/isActivated.php),
 * the function-missing and empty-$loaded branches are therefore not testable
 * in-process without touching that shared bootstrap fixture, which is out of
 * scope for this class — only the true branch is covered. Note: is_activated()
 * excludes can_run_sitemap() entirely — that stays in the untouched
 * get_subscribed_events() and isn't reachable here.
 *
 * @group TheSEOFramework
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	public function testShouldReturnTrueWhenTheSeoFrameworkLoaded() {
		$this->assertTrue( TheSEOFramework::is_activated() );
	}
}
