<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\HealthCheck\CacheDirSizeCheck;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Engine\HealthCheck\CacheDirSizeCheck;

/**
 * @covers \WP_Rocket\Engine\HealthCheck\CacheDirSizeCheck::cache_dir_size_check
 * @group Subscriber
 */
class Test_CacheDirSizeCheck extends TestCase {
	private $cron_option;

	public function setUp() {
		parent::setUp();

		$this->cron_option = get_option( CacheDirSizeCheck::CRON_NAME );
	}

	public function tearDown() {
		parent::tearDown();

		update_option( CacheDirSizeCheck::CRON_NAME, $this->cron_option );
	}

	public function testShouldNotCheckDirSizeWhenOptionIsEnabled() {
		update_option( CacheDirSizeCheck::CRON_NAME, '1foobar' );

		do_action( CacheDirSizeCheck::CRON_NAME );

		$this->assertSame( '1foobar', get_option( CacheDirSizeCheck::CRON_NAME ) );
	}

	public function testShouldCheckDirSizeWhenOptionIsDisabled() {
		delete_option( CacheDirSizeCheck::CRON_NAME );

		do_action( CacheDirSizeCheck::CRON_NAME );

		$this->assertSame( 1, get_option( CacheDirSizeCheck::CRON_NAME ) );
	}
}
