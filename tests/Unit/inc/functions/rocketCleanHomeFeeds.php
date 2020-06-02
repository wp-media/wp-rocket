<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Brain\Monkey\Actions;

/**
 * @covers ::rocket_clean_home_feeds
 * @group Functions
 */
class Test_RocketCleanHomeFeeds extends FilesystemTestCase {
	protected $path_to_test_data   = '/inc/functions/rocketCleanHomeFeeds.php';
	private $home_url;

	public function setUp()
	{
		parent::setUp();

		$home_url = $this->home_url;

		Functions\expect('get_feed_link')->twice()->andReturnUsing( function( $feed = null ) use ( $home_url ) {
			switch ($feed) {
				case 'comments_':
					return $home_url."/feed/comments";
					break;
				default:
					return $home_url."/feed/";
					break;
			}
		} );

		Functions\expect( 'wp_parse_url' )
			->twice()
			->andReturnUsing( function( $url ) {
				return parse_url( $url );
			} );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanHomeFeeds( $config, $expected ) {

		$this->home_url = $config['home_url'];

		$feeds = [
			"/feed/",
			"/feed/comments"
		];

		Actions\expectDone('before_rocket_clean_files')->once()->with($feeds);

		rocket_clean_home_feeds();
	}

	public function setHomeURL($url, $path) {
		return $this->home_url.$path;
	}

}
