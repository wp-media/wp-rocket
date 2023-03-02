<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_clean_home_feeds
 * @uses  ::rocket_direct_filesystem
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketCleanHomeFeeds extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketCleanHomeFeeds.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldExpected( $config, $expected ) {
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		// Run it.
		Functions\expect( 'get_rocket_option' )
			->with( 'cache_feed' )
			->andReturn( $config['cache_feed'] );
		if($config['cache_feed']){
			Functions\expect( 'get_feed_link' )
				->andReturn( $config['urls'][0] );
			Functions\expect( 'get_feed_link' )
				->with( 'comments_' )
				->andReturn( $config['urls'][1] );

			Functions\when( 'wp_parse_url' )->justReturn( null );

			Actions\expectDone( 'before_rocket_clean_home_feeds' )
				->once()->with( $config['cache_feed'] );
			Actions\expectDone( 'after_rocket_clean_home_feeds' )
				->once()->with( $config['cache_feed'] );
		}
		rocket_clean_home_feeds();

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}
