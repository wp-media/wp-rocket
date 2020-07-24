<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdminSubscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * @covers WP_Rocket\Engine\Cache\AdminSubscriber::add_wp_cache_status_test
 *
 * @group  AdminOnly
 * @group  WPCache
 */
class Test_AddWpCacheStatusTest extends AdminTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/WPCache/addWpCacheStatusTest.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddWpCacheTest( $tests, $expected ) {
        $result = apply_filters( 'site_status_tests', $tests );

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

	public function providerTestData() {
		$fixture = require WP_ROCKET_TESTS_FIXTURES_DIR . $this->path_to_test_data;

		return $fixture['test_data'];
	}
}
