<?php
namespace WP_Rocket\Tests\Unit\Admin\Upgrader;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use org\bovigo\vfs\vfsStream,
	org\bovigo\vfs\vfsStreamDirectory;

/**
 * @runTestsInSeparateProcesses
 */
class TestRocketUpgrader extends TestCase {
    private $path;
    private $mock_fs;

    protected function setUp() {
        parent::setUp();

        require WP_ROCKET_PLUGIN_ROOT . 'inc/admin/upgrader.php';

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

    public function testShouldDeleteLicenceDataFileWhenSecretKey() {
        Functions\when('get_rocket_option')->justReturn('3.5');
        Functions\when('flush_rocket_htaccess')->justReturn();
        Functions\when('rocket_renew_all_boxes')->justReturn();
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->justReturn([]);
        Functions\when('rocket_check_key')->justReturn(
            [
                'secret_key' => 'key',
            ]
        );
        Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->mock_fs;
		});
        Functions\when('rocket_valid_key')->justReturn(true);
        Functions\when('current_user_can')->justReturn(true);

		if ( ! defined('WP_ROCKET_VERSION') ) {
			define('WP_ROCKET_VERSION', '3.4');
		}
        define( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );
        define('WP_ROCKET_PATH', $this->path->url() . '/' );

        $this->assertTrue( $this->path->hasChild('licence-data.php') );

        rocket_upgrader();

        $this->assertFalse( $this->path->hasChild('licence-data.php'));
    }

    public function testShouldDeleteLicenceDataFileWhenCheckKeyTrue() {
        Functions\when('get_rocket_option')->justReturn('3.5');
        Functions\when('flush_rocket_htaccess')->justReturn();
        Functions\when('rocket_renew_all_boxes')->justReturn();
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->justReturn([]);
        Functions\when('rocket_check_key')->justReturn(true);
        Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->mock_fs;
		});
        Functions\when('rocket_valid_key')->justReturn(true);
        Functions\when('current_user_can')->justReturn(true);

		if ( ! defined('WP_ROCKET_VERSION') ) {
			define('WP_ROCKET_VERSION', '3.4');
		}
        define( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );
        define('WP_ROCKET_PATH', $this->path->url() . '/' );

        $this->assertTrue( $this->path->hasChild('licence-data.php') );

        rocket_upgrader();

        $this->assertFalse( $this->path->hasChild('licence-data.php'));
    }

    public function testShouldKeepLicenceDataFileWhenNoSecretKey() {
        Functions\when('get_rocket_option')->justReturn('3.5');
        Functions\when('flush_rocket_htaccess')->justReturn();
        Functions\when('rocket_renew_all_boxes')->justReturn();
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->justReturn([]);
        Functions\when('rocket_check_key')->justReturn([]);
        Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->mock_fs;
		});
        Functions\when('rocket_valid_key')->justReturn(true);
        Functions\when('current_user_can')->justReturn(true);

		if ( ! defined('WP_ROCKET_VERSION') ) {
			define('WP_ROCKET_VERSION', '3.4');
		}
        define( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );
        define('WP_ROCKET_PATH', $this->path->url() . '/' );

        $this->assertTrue( $this->path->hasChild('licence-data.php') );

        rocket_upgrader();

        $this->assertTrue( $this->path->hasChild('licence-data.php'));
    }

    public function testShouldKeepLicenceDataFileWhenNoCheckKeyFalse() {
        Functions\when('get_rocket_option')->justReturn('3.5');
        Functions\when('flush_rocket_htaccess')->justReturn();
        Functions\when('rocket_renew_all_boxes')->justReturn();
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->justReturn([]);
        Functions\when('rocket_check_key')->justReturn(false);
        Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->mock_fs;
		});
        Functions\when('rocket_valid_key')->justReturn(false);
        Functions\when('current_user_can')->justReturn(true);

		if ( ! defined('WP_ROCKET_VERSION') ) {
			define('WP_ROCKET_VERSION', '3.4');
		}
        define( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );
        define('WP_ROCKET_PATH', $this->path->url() . '/' );

        $this->assertTrue( $this->path->hasChild('licence-data.php') );

        rocket_upgrader();

        $this->assertTrue( $this->path->hasChild('licence-data.php'));
    }
}
