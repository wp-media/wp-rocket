<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\WPCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\WPCache::update_wp_cache
 * @uses   \rocket_valid_key()
 * @uses   \WP_Rocket\Engine\Cache\WPCache::set_wp_cache_constant
 *
 * @group  WPCache
 */
class Test_UpdateWPCache extends TestCase {

	public function testShouldBailOutWhenNotRockedValidKey() {
		$wp_cache = new WPCache( null );

		Functions\expect( 'rocket_valid_key' )
			->once()
			->andReturn( false );

		$this->assertNull( $wp_cache->update_wp_cache() );
	}

	public function testShouldCallSetCacheConstant() {
		$wp_cache = new WPCache( null );

		Functions\expect( 'rocket_valid_key' )
			->once()
			->andReturn( true );
		Functions\expect( 'current_user_can' )->andReturn( false );

		$wp_cache->update_wp_cache();
	}

	/**
	 * @group Multisite
	 */
	public function testShouldNotUpdateWhenMultisiteAndSitesNotZero() {
		$wp_cache = new WPCache( null );

		Functions\expect( 'rocket_valid_key' )
			->once()
			->andReturn( true );
		Functions\when( 'current_filter' )->justReturn( 'rocket_deactivation' );
		Functions\when( 'is_multisite' )->justReturn( true );

		$this->assertNull( $wp_cache->update_wp_cache( 1 ) );
	}
}
