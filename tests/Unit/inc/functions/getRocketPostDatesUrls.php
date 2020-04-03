<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::get_rocket_post_dates_urls
 * @group  Posts
 * @group  Functions
 */
class Test_GetRocketPostDatesUrls extends TestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/posts.php';
	}

	protected function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['wp_rewrite'] );
	}

	public function testShouldBailOutWhenPostDoesNotExist() {
		Functions\expect( 'get_the_time' )
			->once()
			->with( 'Y-m-d', - 1 )
			->andReturn( false );
		Functions\expect( 'get_year_link' )->never();
		Functions\expect( 'get_month_link' )->never();
		Filters\expectApplied( 'rocket_post_dates_urls' )->never();

		$this->assertSame( [], get_rocket_post_dates_urls( - 1 ) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGetPostDatesUrls( $post_data, $expected ) {
		$GLOBALS['wp_rewrite'] = (object) [ 'pagination_base' => 'page' ];
		$post_id               = 10;
		$date                  = explode( '-', $post_data['post_date'] );
		$date[1]               = sprintf( '%02s', $date[1] );

		Functions\expect( 'get_the_time' )
			->once()
			->with( 'Y-m-d', $post_id )
			->andReturn( $post_data['post_date'] );
		Functions\expect( 'get_year_link' )
			->once()
			->with( $date[0] )
			->andReturn( "http://example.org/{$date[0]}" );
		Functions\expect( 'get_month_link' )
			->once()
			->with( $date[0], $date[1] )
			->andReturn( "http://example.org/{$date[0]}/{$date[1]}" );
		Functions\expect( 'get_day_link' )
			->once()
			->with( $date[0], $date[1], $date[2] )
			->andReturn( "http://example.org/{$date[0]}/{$date[1]}/{$date[2]}/" );
		Filters\expectApplied( 'rocket_post_dates_urls' )
			->once()
			->with( $expected );

		$this->assertSame( $expected, get_rocket_post_dates_urls( $post_id ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'getRocketPostDatesUrls' );
	}
}
