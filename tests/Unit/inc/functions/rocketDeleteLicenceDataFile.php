<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_delete_licence_data_file
 * @group Functions
 * @group Options
 */
class Test_RocketDeleteLicenceDataFile extends FilesystemTestCase {
	protected $rootVirtualDir = 'wp-rocket';
	protected $structure      = [
		'licence-data.php' => '',
	];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php';
	}

	public function testShouldDeleteLicenceDataFileWhenExists() {
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_PATH' )
			->andReturn( $this->rootVirtualUrl );

		$filename = $this->filesystem->getUrl( 'licence-data.php' );
		$this->assertFileExists( $filename );

		rocket_delete_licence_data_file();

		$this->assertFileNotExists( $filename );
	}
}
