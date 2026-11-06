<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::rocket_delete_licence_data_file
 * @group Functions
 * @group Options
 * @group vfs
 */
class Test_RocketDeleteLicenceDataFile extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketDeleteLicenceDataFile.php';

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php';
	}

	public function testShouldDeleteLicenceDataFileWhenExists() {
		Functions\when( 'is_multisite' )->justReturn( false );

		$this->assertTrue( $this->filesystem->exists( 'wp-content/plugins/wp-rocket/licence-data.php' ) );

		rocket_delete_licence_data_file();

		$this->assertFalse( $this->filesystem->exists( 'wp-content/plugins/wp-rocket/licence-data.php' ) );
	}

	public function testShouldDoNothingWhenMultisite() {
		Functions\when( 'is_multisite' )->justReturn( true );

		$this->assertTrue( $this->filesystem->exists( 'wp-content/plugins/wp-rocket/licence-data.php' ) );

		rocket_delete_licence_data_file();

		$this->assertTrue( $this->filesystem->exists( 'wp-content/plugins/wp-rocket/licence-data.php' ) );
	}
}
