<?php
namespace WP_Rocket\Tests\Integration\Functions\Options;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers rocket_delete_licence_data_file
 * @group Functions
 * @group Options
 */
class Test_RocketDeleteLicenceDataFile extends FilesystemTestCase {
	private $path;
	protected $rootVirtualDir = 'wp-rocket';
	protected $structure = [
		'licence-data.php' => '',
	];

	public function testShouldDeleteLicenceDataFileWhenExists() {
		Functions\when( 'rocket_get_constant' )
			->justReturn( $this->path->url() . '/' );

		$this->assertTrue( $this->filesystem->exists( 'licence-data.php' ) );

		rocket_delete_licence_data_file();

		$this->assertFalse( $this->filesystem->exists( 'licence-data.php' ) );
	}
}
