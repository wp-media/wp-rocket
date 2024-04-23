<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\WPCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

/**
 * Test class covering \WP_Rocket\Engine\Cache\WPCache::add_wp_cache_status_test
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
	public function testShouldAddWpCacheTest( $config, $tests, $expected ) {
		$wp_cache = new WPCache( null );

		if ( isset( $config['filter_constant_value'] ) ) {
			Filters\expectApplied( 'rocket_set_wp_cache_constant' )->andReturn( false );
		}

		$result = $wp_cache->add_wp_cache_status_test( $tests );

		if ( isset( $expected['direct'] ) ){
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
		else{
			$this->assertArrayNotHasKey( 'wp_cache_status', $result['direct'] );
		}
		
	}
}
