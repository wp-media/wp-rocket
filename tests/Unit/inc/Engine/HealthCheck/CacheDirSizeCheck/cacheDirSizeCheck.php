<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\HealthCheck\CacheDirSizeCheck;

use Brain\Monkey\Functions;
use org\bovigo\vfs\content\LargeFileContent;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\HealthCheck\CacheDirSizeCheck;

/**
 * @covers \WP_Rocket\Engine\HealthCheck\CacheDirSizeCheck::cache_dir_size_check
 * @group Subscriber
 * @group HealthCheck
 */
class Test_CacheDirSizeCheck extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/HealthCheck/CacheDirSizeCheck/cacheDirSizeCheck.php';
	private $subscriber;

	public function setUp() {
		parent::setUp();

		Functions\expect( 'get_current_blog_id' )
			->once()
			->andReturn( 1 );

		$this->subscriber = new CacheDirSizeCheck(
			$this->filesystem->getUrl( 'wp-content/cache/min/' ),
			'https://wp-rocket.me/',
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCheckDirSizeWhenOptionIsDisabled( $option_value, $dir_size_excess ) {
		Functions\when( 'get_option' )->justReturn( $option_value );

		if ( ! $option_value ) {
			Functions\expect( 'update_option' )
				->once()
				->with( 'rocket_cache_dir_size_check', 1 );
		} else {
			Functions\expect( 'get_current_blog_id' )
				->never();
			Functions\expect( 'update_option' )
				->never();
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

		$this->subscriber->cache_dir_size_check();
	}
}
