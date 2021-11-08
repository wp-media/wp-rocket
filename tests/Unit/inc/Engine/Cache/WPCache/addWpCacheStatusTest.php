<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\WPCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\WPCache::add_wp_cache_status_test
 *
 * @group WPCache
 */
class Test_AddWpCacheStatusTest extends TestCase {
	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddWpCacheTest( $tests, $expected ) {
		$wp_cache = new WPCache( null );

		$result = $wp_cache->add_wp_cache_status_test( $tests );

		$this->assertArrayHasKey(
			'wp_cache_status',
			$result['direct']
		);

		$this->assertSame(
			$expected['direct']['wp_cache_status']['label'],
			$result['direct']['wp_cache_status']['label']
		);

		$this->assertTrue( is_callable( $result['direct']['wp_cache_status']['test'] ) );
	}
}
