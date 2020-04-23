<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\HealthCheck\CacheDirSizeCheck;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\HealthCheck\CacheDirSizeCheck;

/**
 * @covers \WP_Rocket\Engine\HealthCheck\CacheDirSizeCheck::cache_dir_size_check
 * @group Subscriber
 */
class Test_CacheDirSizeCheck extends TestCase {
	private $subscriber;

	public function setUp() {
		parent::setUp();

		$this->subscriber = new CacheDirSizeCheck();
	}

	public function testShouldNotCheckDirSizeWhenOptionIsEnabled() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'rocket_cache_dir_size_check' )
			->andReturn( true );
		Functions\expect( 'get_current_blog_id' )
			->never();
		Functions\expect( 'update_option' )
			->never();

		$this->subscriber->cache_dir_size_check();
	}

	public function testShouldCheckDirSizeWhenOptionIsDisabled() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'rocket_cache_dir_size_check' )
			->andReturn( false );
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( 'wp-content/cache/' );
		Functions\expect( 'get_current_blog_id' )
			->once();
		Functions\expect( 'update_option' )
			->once()
			->with( 'rocket_cache_dir_size_check', 1 );

		$this->subscriber->cache_dir_size_check();
	}
}
