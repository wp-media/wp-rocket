<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\WPCache;

use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\AdvancedCache::activate
 *
 * @group  WPCache
 */
class Test_Activate extends TestCase {
	public function testShouldSetCorrectHooks() {
		$wp_cache = new WPCache( null );

		$wp_cache->activate();

		$this->assertEquals(
			10,
			has_action( 'rocket_activation', [ $wp_cache, 'update_wp_cache' ] )
		);
	}
}
