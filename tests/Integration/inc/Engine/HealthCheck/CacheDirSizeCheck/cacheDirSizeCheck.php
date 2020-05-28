<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\HealthCheck\CacheDirSizeCheck;

use Brain\Monkey\Functions;
use org\bovigo\vfs\content\LargeFileContent;
use WP_Rocket\Engine\HealthCheck\CacheDirSizeCheck;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\HealthCheck\CacheDirSizeCheck::cache_dir_size_check
 *
 * @group Subscriber
 * @group HealthCheck
 */
class Test_CacheDirSizeCheck extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/HealthCheck/CacheDirSizeCheck/cacheDirSizeCheck.php';

	public function tearDown() {
		parent::tearDown();

		delete_option( CacheDirSizeCheck::CRON_NAME );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCheckDirSizeWhenOptionIsDisabled( $option_value, $dir_size_excess ) {
		if ( $option_value ) {
			update_option( CacheDirSizeCheck::CRON_NAME, $option_value );
		}

		if ( $dir_size_excess ) {
			$this->filesystem->put_contents( 'public/wp-content/cache/min/1/large.js', LargeFileContent::withGigabytes( 11 ) );

			Functions\expect( 'wp_safe_remote_post' )
			->once()
			->with(
				'https://wp-rocket.me/api/wp-rocket/cache-dir-check.php',
				[
					'body' => 'cache_dir_type=min',
				]
			);
		} else {
			Functions\expect( 'wp_safe_remote_post' )
			->never();
		}

		do_action( CacheDirSizeCheck::CRON_NAME );

		$this->assertSame( true, (bool) get_option( CacheDirSizeCheck::CRON_NAME ) );
	}
}
