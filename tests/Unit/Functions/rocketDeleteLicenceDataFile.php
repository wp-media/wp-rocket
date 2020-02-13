<?php
namespace WP_Rocket\Tests\Unit\Functions\Options;

use WPMedia\PHPUnit\Unit\TestCase;
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
	private $mock_fs;

	public function setUp() {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php';

		$structure = [
			'licence-data.php' => '',
		];

		$this->path = vfsStream::setup( 'wp-rocket', null, $structure );

		$this->mock_fs = $this->getMockBuilder( 'WP_Filesystem_Direct' )
							->setMethods( [
								'exists',
								'delete'
							] )
							->getMock();
		$this->mock_fs->method( 'exists' )->will( $this->returnCallback( 'file_exists' ) );
		$this->mock_fs->method( 'delete' )->will( $this->returnCallback( function( $file ) {
				unlink( $file );
		}));
	}

	/**
	 * Test should delete the licence-data.php file if it exists
	 */
	public function testShouldDeleteLicenceDataFileWhenExists() {
		Functions\when( 'rocket_get_constant' )
			->justReturn( $this->path->url() . '/' );
		Functions\when( 'rocket_direct_filesystem')->alias( function() {
			return $this->mock_fs;
		});

		$this->assertTrue( $this->path->hasChild( 'licence-data.php' ) );

		rocket_delete_licence_data_file();

		$this->assertFalse( $this->path->hasChild( 'licence-data.php' ) );
	}
}
