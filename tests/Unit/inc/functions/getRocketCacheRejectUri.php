<?php
namespace WP_Rocket\Tests\Unit\inc\functions;

use stdClass;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::get_rocket_cache_reject_uri
 * @group Functions
 * @group Options
 */
class Test_GetRocketCacheRejectUri extends TestCase {

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['wp_rewrite'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGetRocketCacheRejectUri( $config, $expected ) {
		$GLOBALS['wp_rewrite']            = new stdClass();
		$GLOBALS['wp_rewrite']->feed_base = 'feed/';

		Functions\expect( 'get_rocket_option' )
			->once()
			->with( 'cache_reject_uri', [] )
			->andReturn( $config['options']['cache_reject_uri'] );

		Functions\when( 'rocket_get_home_dirname' )->justReturn( $config['home_dirname'] );

		Functions\expect( 'apply_filters' )
			->once()
			->andReturn( (array) $config['filter_rocket_cache_reject_uri'] );

		$this->assertSame(
			$expected,
			get_rocket_cache_reject_uri( true )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'getRocketCacheRejectUri' );
	}
}
