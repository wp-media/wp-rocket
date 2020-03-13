<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_delete_licence_data_file
 * @group Functions
 * @group Options
 */
class Test_RocketDeleteLicenceDataFile extends FilesystemTestCase {
	protected $rootVirtualDir = 'wp-rocket';
	protected $structure = [
		'licence-data.php' => '',
	];

	public function testShouldDeleteLicenceDataFileWhenExists() {
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_PATH' )
			->andReturn( $this->rootVirtualUrl . '/' );

		$this->assertTrue( $this->filesystem->exists( 'licence-data.php' ) );

		rocket_delete_licence_data_file();

		$this->assertFalse( $this->filesystem->exists( 'licence-data.php' ) );
	}

	/**
	 * @group Multisite
	 */
	public function testShouldDoNothingWhenMultisite() {
		$this->assertTrue( $this->filesystem->exists( 'wp-rocket/licence-data.php' ) );

		rocket_delete_licence_data_file();

		$this->assertTrue( $this->filesystem->exists( 'wp-rocket/licence-data.php' ) );
	}
}
