<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\WPCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\WPCache::update_wp_cache
 *
 * @group  WPCache
 */
class Test_UpdateWPCache extends TestCase {
	public function testShouldBailOutWhenNotRockedValidKey() {
		$wp_cache = new WPCache( null );

		Functions\expect( 'rocket_valid_key' )
			->once()
			->andReturn( false );

		$wp_cache->update_wp_cache();
	}

	public function testShouldCallSetCacheConstant() {
		$wp_cache = new WPCache( null );

		Functions\expect( 'rocket_valid_key' )
			->once()
			->andReturn( true );
		Functions\expect( 'current_user_can' )->once();

		$wp_cache->update_wp_cache();
	}

	/**
	 * @group Multisite
	 */
	public function testShouldNotUpdateWhenMultisiteAndSitesNotZero() {
		$this->markTestSkipped( 'Test doest not perform assertion, need to revisit' );

		$wp_cache = new WPCache( null );

		Functions\when( 'current_filter' )->justReturn( 'rocket_deactivation' );

		$wp_cache->update_wp_cache( 1 );
	}
}
