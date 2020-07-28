<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\WPCache;

use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\AdvancedCache::deactivate
 *
 * @group  WPCache
 */
class Test_Deactivate extends TestCase {
	public function testShouldSetCorrectHooks() {
		$wp_cache = new WPCache( null );

		$wp_cache->deactivate();

		$this->assertEquals(
			10,
			has_action( 'rocket_deactivation', [ $wp_cache, 'update_wp_cache' ] )
		);

		$this->assertEquals(
			10,
			has_action( 'rocket_prevent_deactivation', [ $wp_cache, 'maybe_prevent_deactivation' ] )
		);
	}
}
