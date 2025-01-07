<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\WPCache;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\WPCache::notice_wp_config_permissions
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_notice_html
 *
 * @group  WPCache
 */
class Test_NoticeWpConfigPermissions extends FileSystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/WPCache/noticeWpConfigPermissions.php';

	protected function setUp(): void {
		parent::setUp();

        Functions\when( 'wp_create_nonce' )->justReturn( '123456' );
    }

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEchoNotice( $config, $expected ) {
		$this->wp_cache_constant = $config['constant'];
		$this->setUpMocks( $config );

		$wp_cache = new WPCache( $this->filesystem );

		if ( empty( $expected ) ) {
			Functions\expect( 'rocket_notice_html' )->never();
		} else {
			Functions\expect( 'rocket_notice_writing_permissions' )
				->once()
				->with( 'wp-config.php' )
				->andReturn( $config['message'] );

			Functions\expect( 'rocket_notice_html' )
				->once()
				->with(
					[
						'status'           => 'error',
						'dismissible'      => '',
						'message'          => $config['message'],
						'dismiss_button'   => 'rocket_warning_wp_config_permissions',
						'readonly_content' => "define( 'WP_CACHE', true ); // Added by WP Rocket",
					]
				)
				->andReturnNull();
		}

		$wp_cache->notice_wp_config_permissions();
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

		if ( isset( $config['filter'] ) ) {
			Filters\expectApplied( 'rocket_set_wp_cache_constant' )
				->once()
				->andReturn( $config['filter'] );
		}

		if ( ! $config['writable'] ) {
			$this->filesystem->chmod( 'wp-config.php', 0444 );
		}

		if ( $config['writable'] || $this->wp_cache_constant ) {
			return;
		}

		Functions\expect( 'get_current_user_id' )->once()->andReturn( 1 );
		Functions\expect( 'get_user_meta' )
			->once()
			->with( 1, 'rocket_boxes', true )
			->andReturn( $config['boxes'] );
	}
}
