<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering ::rocket_delete_licence_data_file
 * @group Functions
 * @group Options
 * @group vfs
 */
class Test_RocketDeleteLicenceDataFile extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketDeleteLicenceDataFile.php';

	public function testShouldDeleteLicenceDataFileWhenExists() {
		$this->assertTrue( $this->filesystem->exists( 'wp-content/plugins/wp-rocket/licence-data.php' ) );

		rocket_delete_licence_data_file();

		$this->assertFalse( $this->filesystem->exists( 'wp-content/plugins/wp-rocket/licence-data.php' ) );
	}

	/**
	 * @group Multisite
	 */
	public function testShouldDoNothingWhenMultisite() {
		$this->assertTrue( $this->filesystem->exists( 'wp-content/plugins/wp-rocket/licence-data.php' ) );

		rocket_delete_licence_data_file();

		$this->assertTrue( $this->filesystem->exists( 'wp-content/plugins/wp-rocket/licence-data.php' ) );
	}
}
