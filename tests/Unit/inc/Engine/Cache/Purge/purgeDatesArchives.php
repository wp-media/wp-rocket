<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\Purge;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\Purge;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;

/**
 * Test class covering \WP_Rocket\Engine\Cache\Purge::purge_dates_archives
 * @group  purge_actions
 */
class Test_PurgeDatesArchives extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/Purge/purgeDatesArchives.php';

	protected function setUp(): void {
		parent::setUp();
		
		$query = $this->createPartialMock(Cache::class, ['query']);
		$this->purge = new Purge( $this->filesystem, $query );
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wp_rewrite'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanCache( $post, $cleaned ) {
		$GLOBALS['wp_rewrite'] = (object) [ 'pagination_base' => 'page' ];
		$post['ID']            = 1;
		$post                  = (object) $post;
		$date                  = explode( '-', $post->post_date );

		$this->generateEntriesShouldExistAfter( $cleaned );

		Functions\expect( 'get_the_time' )
			->once()
			->with( 'Y-m-d', $post )
			->andReturn( $post->post_date );
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

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );

		$this->purge->purge_dates_archives( $post );

		$this->checkEntriesDeleted( $cleaned );
		$this->checkShouldNotDeleteEntries();
   }
}
