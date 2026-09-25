<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Presslabs;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Presslabs;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Presslabs::clean_home
 *
 * clean_home() invalidates the Presslabs cache for the homepage URL. See issue #8768.
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

	public function testShouldInvalidateHomeUrl() {
		Functions\when( 'home_url' )->justReturn( 'https://example.com/' );

		$cache_handler = Mockery::mock( 'overload:Presslabs\Cache\CacheHandler' );
		$cache_handler->shouldReceive( 'invalidate_url' )
			->once()
			->with( 'https://example.com/', true );

		( new Presslabs() )->clean_home( '/cache/root/', 'en_US' );
	}
}
