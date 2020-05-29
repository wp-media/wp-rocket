<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_home()
 * @group Functions
 * @group vfs
 */
class Test_RocketCleanHome extends FilesystemTestCase {
	protected $path_to_test_data   = '/inc/functions/rocketCleanHome.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanHome( $config, $expected ) {
		Functions\when( 'home_url' )->justReturn( $config['home_url'] );
		rocket_clean_home('', $this->filesystem);

		foreach ($expected['removed_files'] as $removed_file) {
			$this->assertFalse( $this->filesystem->exists( $this->config['vfs_dir'].$removed_file ) );
		}

		foreach ($expected['not_removed_files'] as $not_removed_file) {
			$this->assertTrue( $this->filesystem->exists( $this->config['vfs_dir'].$not_removed_file ) );
		}
	}

}
