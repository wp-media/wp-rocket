<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use stdClass;

/**
 * @covers ::rocket_clean_home()
 * @group Functions
 */
class Test_RocketCleanHome extends FilesystemTestCase {
	protected $path_to_test_data   = '/inc/functions/rocketCleanHome.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanHome( $config, $expected ) {
		Functions\when( 'home_url' )->justReturn( $config['home_url'] );
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );
		$GLOBALS['wp_rewrite']                  = new stdClass();
		$GLOBALS['wp_rewrite']->pagination_base = 'page';

		rocket_clean_home('', $this->filesystem);

		foreach ($expected['removed_files'] as $removed_file) {
			$this->assertFalse( $this->filesystem->exists( $this->config['vfs_dir'].$removed_file ) );
		}

		foreach ($expected['not_removed_files'] as $not_removed_file) {
			$this->assertTrue( $this->filesystem->exists( $this->config['vfs_dir'].$not_removed_file ) );
		}
	}

}
