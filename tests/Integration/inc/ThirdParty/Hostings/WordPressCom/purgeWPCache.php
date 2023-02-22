<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WordPressCom;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WordPressCom::purge_wp_cache
 *
 * @group  WordPressCom
 * @group  ThirdParty
 * @group  Hostings
 */
class Test_PurgeWpCache extends TestCase {
	public function testShouldPurgeWpCache( ) {
		wp_cache_set( 'homepage_cache', 'Homepage content' );

		do_action( 'after_rocket_clean_domain' );

		$this->assertFalse( wp_cache_get( 'homepage_cache' ) );
	}
}
