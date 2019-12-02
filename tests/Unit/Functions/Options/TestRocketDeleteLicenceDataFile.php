<?php
namespace WP_Rocket\Tests\Unit\Functions\Options;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use org\bovigo\vfs\vfsStream,
	org\bovigo\vfs\vfsStreamDirectory;

/**
 * @runTestsInSeparateProcesses
 */
class TestRocketDeleteLicenceDataFile extends TestCase {
    private $path;
    private $mock_fs;

    protected function setUp() {
        parent::setUp();

        require WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php';

        $structure = [
            'licence-data.php' => '',
        ];

        $this->path = vfsStream::setup( 'wp-rocket', null, $structure );

        $this->mock_fs = $this->getMockBuilder( 'WP_Filesystem_Direct' )
							->setMethods( [
								'exists',
								'delete'
							])
							->getMock();
		$this->mock_fs->method('exists')->will($this->returnCallback('file_exists'));
		$this->mock_fs->method('delete')->will($this->returnCallback(function($file) {
				unlink( $file );
		}));
    }

    public function testShouldDeleteLicenceDataFileWhenExists() {
        define('WP_ROCKET_PATH', $this->path->url() . '/' );

        Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->mock_fs;
        });

        $this->assertTrue( $this->path->hasChild('licence-data.php') );

        rocket_delete_licence_data_file();

        $this->assertFalse( $this->path->hasChild('licence-data.php'));
    }
}