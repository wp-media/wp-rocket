<?php
namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Tools\CacheDirSizeCheckSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Tests\Fixtures\inc\classes\subscriber\Tools\CacheDirSizeCheckSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\Tools\Cache_Dir_Size_Check_Subscriber::cache_dir_size_check
 * @group Subscriber
 */
class Test_CacheDirSizeCheck extends TestCase {
	private $subscriber;

	public function setUp() {
		parent::setUp();

		$this->subscriber = new CacheDirSizeCheckSubscriber();
	}

	public function testShouldNotCheckDirSizeWhenOptionIsEnabled() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'rocket_cache_dir_size_check' )
			->andReturn( true );
		Functions\expect( 'get_current_blog_id' )
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
