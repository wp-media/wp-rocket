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

	public function tear_down() {
		remove_filter( 'rocket_set_wp_cache_constant', [ $this, 'return_false' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddWpCacheTest( $config, $tests, $expected ) {

        if ( isset( $config['filter_constant_value'] ) ) {
			add_filter( 'rocket_set_wp_cache_constant', [ $this, 'return_false' ] );
		}

		$result = apply_filters( 'site_status_tests', $tests );

        if ( isset( $expected['direct'] ) ) {
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

	public function providerTestData() {
		$fixture = require WP_ROCKET_TESTS_FIXTURES_DIR . $this->path_to_test_data;

		return $fixture['test_data'];
	}
}
