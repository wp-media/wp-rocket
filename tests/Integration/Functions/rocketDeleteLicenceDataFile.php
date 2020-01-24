<?php
namespace WP_Rocket\Tests\Integration\Functions\Options;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
use org\bovigo\vfs\vfsStream,
	org\bovigo\vfs\vfsStreamDirectory;

/**
 * @covers rocket_delete_licence_data_file
 * @group Functions
 * @group Options
 */
class Test_RocketDeleteLicenceDataFile extends TestCase {
	private $path;

	public function setUp() {
		parent::setUp();

		$structure = [
			'licence-data.php' => '',
		];

		$this->path = vfsStream::setup( 'wp-rocket', null, $structure );
	}

	/**
	 * Test should delete the licence-data.php file if it exists
	 */
	public function testShouldDeleteLicenceDataFileWhenExists() {
		Functions\when( 'rocket_get_constant' )
			->justReturn( $this->path->url() . '/' );

		$this->assertTrue( $this->path->hasChild( 'licence-data.php' ) );

		rocket_delete_licence_data_file();

		$this->assertFalse( $this->path->hasChild( 'licence-data.php' ) );
	}
}
