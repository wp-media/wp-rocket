<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\AdvancedCache::notice_permissions
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_notice_html
 *
 * @group  AdvancedCache
 */
class Test_NoticePermissions extends FileSystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/AdvancedCache/noticePermissions.php';

	public function setUp() : void {
		parent::setUp();

		Functions\when( 'wp_create_nonce' )->justReturn( '123456' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEchoNotice( $config, $expected ) {
		$this->wp_rocket_advanced_cache = $config['constant'];
		$this->setUpMocks( $config );

		$advanced_cache = Mockery::mock(
			AdvancedCache::class . '[get_advanced_cache_content]',
			[ null, $this->filesystem ]
		);

		if ( empty( $expected ) ) {
			$advanced_cache->shouldReceive( 'get_advanced_cache_content' )->never();
			Functions\expect( 'rocket_notice_html' )->never();
		} else {
			$advanced_cache->shouldReceive( 'get_advanced_cache_content' )
			               ->once()
			               ->andReturn( '' );

			Functions\expect( 'rocket_notice_writing_permissions' )
				->once()
				->with( 'wp-content/advanced-cache.php' )
				->andReturn( $config['message'] );

			Functions\expect( 'rocket_notice_html' )
				->once()
				->with(
					[
						'status'           => 'error',
						'dismissible'      => '',
						'message'          => $config['message'],
						'dismiss_button'   => 'rocket_warning_advanced_cache_permissions',
						'readonly_content' => '',
					]
				)
				->andReturnNull();
		}

		$advanced_cache->notice_permissions();
	}

	private function setUpMocks( $config ) {
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( $config['cap'] );
		if ( $config['cap'] ) {
			Functions\expect( 'rocket_valid_key' )
				->once()
				->andReturn( $config['valid_key'] );
		} else {
			Functions\expect( 'rocket_valid_key' )->never();
		}

		if ( ! $config['writable'] ) {
			$this->filesystem->chmod( 'wp-content/advanced-cache.php', 0444 );
		}

		if ( $config['writable'] || $this->wp_rocket_advanced_cache ) {
			return;
		}

		Functions\expect( 'get_current_user_id' )->once()->andReturn( 1 );
		Functions\expect( 'get_user_meta' )
			->once()
			->with( 1, 'rocket_boxes', true )
			->andReturn( $config['boxes'] );
	}
}
