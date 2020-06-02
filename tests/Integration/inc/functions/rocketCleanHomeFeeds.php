<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

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

		add_filter( 'home_url', [ $this, 'setHomeURL' ], 10, 2 );

		$this->set_permalink_structure( "/%postname%/" );
	}

	public function tearDown()
	{
		remove_filter( 'home_url', [ $this, 'setHomeURL' ], 10, 2 );
		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanHomeFeeds( $config, $expected ) {

		$this->home_url = $config['home_url'];

		rocket_clean_home_feeds();

		foreach ($expected['removed_files'] as $removed_file) {
			$this->assertFalse( $this->filesystem->exists( $this->config['vfs_dir'].$removed_file ) );
		}

		foreach ($expected['not_removed_files'] as $not_removed_file) {
			$this->assertTrue( $this->filesystem->exists( $this->config['vfs_dir'].$not_removed_file ) );
		}
	}

	public function setHomeURL($url, $path) {
		return $this->home_url.$path;
	}

}
