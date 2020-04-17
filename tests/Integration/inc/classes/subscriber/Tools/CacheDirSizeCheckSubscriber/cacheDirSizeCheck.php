<?php
namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Tools\CacheDirSizeCheckSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Subscriber\Tools\Cache_Dir_Size_Check_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Tools\Cache_Dir_Size_Check_Subscriber::cache_dir_size_check
 * @group Subscriber
 */
class Test_CacheDirSizeCheck extends TestCase {
	private $cron_option;

	public function setUp() {
		parent::setUp();

		$this->cron_option = get_option( Cache_Dir_Size_Check_Subscriber::CRON_NAME );
	}

	public function tearDown() {
		parent::tearDown();

		update_option( Cache_Dir_Size_Check_Subscriber::CRON_NAME, $this->cron_option );
	}

	public function testShouldNotCheckDirSizeWhenOptionIsEnabled() {
		update_option( Cache_Dir_Size_Check_Subscriber::CRON_NAME, '1foobar' );

		do_action( Cache_Dir_Size_Check_Subscriber::CRON_NAME );

		$this->assertSame( '1foobar', get_option( Cache_Dir_Size_Check_Subscriber::CRON_NAME ) );
	}

	public function testShouldCheckDirSizeWhenOptionIsDisabled() {
		delete_option( Cache_Dir_Size_Check_Subscriber::CRON_NAME );

		do_action( Cache_Dir_Size_Check_Subscriber::CRON_NAME );

		$this->assertSame( 1, get_option( Cache_Dir_Size_Check_Subscriber::CRON_NAME ) );
	}
}
