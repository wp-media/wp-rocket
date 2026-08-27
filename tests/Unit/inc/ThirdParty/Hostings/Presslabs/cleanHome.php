<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Presslabs;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Presslabs;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Presslabs::clean_home
 *
 * Pinned regression for the pre-existing bug ported verbatim from legacy presslabs.php: the method
 * checks undefined `$post`/`$permalink` instead of its own `$root`/`$lang` parameters, so the guard
 * always returns early and the method body never executes, regardless of the `$root`/`$lang` values
 * passed in. See issue #8768 — do not fix, only pin.
 *
 * @group Presslabs
 * @group ThirdParty
 * @group Hostings
 */
class Test_CleanHome extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Presslabs' );
		}
	}

	public function testShouldAlwaysReturnEarlyRegardlessOfArguments() {
		$cache_handler = Mockery::mock( 'overload:Presslabs\Cache\CacheHandler' );
		$cache_handler->shouldReceive( 'invalidate_url' )->never();

		// The pinned bug references undefined $post/$permalink, which raises a PHP warning on PHP 8+;
		// suppressed here (not in production code) purely so the test harness (which converts warnings
		// to exceptions) can assert the resulting no-op behaviour instead of erroring on the warning itself.
		@( ( new Presslabs() )->clean_home( '/cache/root/', 'en_US' ) );
	}
}
