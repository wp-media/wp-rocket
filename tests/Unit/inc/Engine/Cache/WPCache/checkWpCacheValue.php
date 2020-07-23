<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\WPCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\WPCache::check_wp_cache_value
 *
 * @group WPCache
 */
class Test_CheckWpCacheValue extends TestCase {
    protected static $mockCommonWpFunctionsInSetUp = true;

    /**
	 * @dataProvider configTestData
	 */
	public function testShouldMaybeAddWpCacheConstant( $wp_cache, $expected ) {
        $this->wp_cache_constant = $wp_cache;

        $wp_cache = new WPCache( null );

        $this->assertSame(
            $expected,
            $wp_cache->check_wp_cache_value()
        );
    }
}
