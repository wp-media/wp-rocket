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

	protected function tearDown(): void {
		// Reset after each test.
		remove_filter( 'rocket_cache_reject_uri', 'wp_rocket_cache_feed' );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldExpected( $config, $expected ) {
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		if($config['cache_feed']){
			add_filter( 'rocket_cache_reject_uri', 'wp_rocket_cache_feed' );
			Functions\expect( 'get_feed_link' )
				->andReturn( $config['urls'][0] );
			Functions\expect( 'get_feed_link' )
				->with( 'comments_' )
				->andReturn( $config['urls'][1] );

			Functions\when( 'wp_parse_url' )->justReturn( null );

			Functions\expect('rocket_clean_files');

			Actions\expectDone( 'before_rocket_clean_home_feeds' )
				->once()->with( true );
			Actions\expectDone( 'after_rocket_clean_home_feeds' )
				->once()->with( true );
		}
		rocket_clean_home_feeds();

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}
