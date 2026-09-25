<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Presslabs;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Presslabs;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Presslabs::clean_post
 *
 * @group Presslabs
 * @group ThirdParty
 * @group Hostings
 */
class Test_CleanPost extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Presslabs' );
		}
	}

	public function testShouldDoNothingWhenPostOrPermalinkMissing() {
		$cache_handler = Mockery::mock( 'overload:Presslabs\Cache\CacheHandler' );
		$cache_handler->shouldReceive( 'invalidate_url' )->never();
		$cache_handler->shouldReceive( 'purge_cache' )->never();

		( new Presslabs() )->clean_post( false, false );
	}

	public function testShouldInvalidateAndPurgeWhenPostAndPermalinkProvided() {
		Functions\when( 'home_url' )->justReturn( 'http://example.org/' );

		$cache_handler = Mockery::mock( 'overload:Presslabs\Cache\CacheHandler' );
		$cache_handler->shouldReceive( 'invalidate_url' )->with( 'http://example.org/hello-world/', true )->once();
		$cache_handler->shouldReceive( 'invalidate_url' )->with( 'http://example.org/', true )->once();
		$cache_handler->shouldReceive( 'purge_cache' )->with( 'listing' )->once();

		( new Presslabs() )->clean_post( (object) [ 'ID' => 1 ], [ 'http://example.org/hello-world/' ] );
	}
}
