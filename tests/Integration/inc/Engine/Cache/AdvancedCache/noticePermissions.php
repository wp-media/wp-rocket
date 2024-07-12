<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\AdvancedCache::notice_permissions
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_notice_html
 * @uses   ::rocket_direct_filesystem
 * @uses   ::is_rocket_generate_caching_mobile_files
 *
 * @group  AdminOnly
 * @group  AdvancedCache
 */
class Test_NoticePermissions extends FilesystemTestCase {
	use CapTrait;

	protected $path_to_test_data = '/inc/Engine/Cache/AdvancedCache/noticePermissions.php';

	private static $user_id;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::setAdminCap();
		self::$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
	}

	public function set_up() {
		parent::set_up();

		Functions\when( 'wp_create_nonce' )->justReturn( '123456' );
	}

	public function tear_down() {
		delete_user_meta( get_current_user_id(), 'rocket_boxes', [ 'rocket_warning_advanced_cache_permissions' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEchoNotice( $config, $expected ) {
		$this->wp_rocket_advanced_cache = $config['constant'];
		if ( $config['cap'] ) {
			wp_set_current_user( self::$user_id );
		}
		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );

		if ( ! $config['writable'] ) {
			$this->filesystem->chmod( 'wp-content/advanced-cache.php', 0444 );
		}

		if ( $config['constant'] ) {
			$this->filesystem->put_contents( 'wp-content/advanced-cache.php', 'WP_ROCKET_ADVANCED_CACHE' );
		}

		if ( $config['boxes'] ) {
			add_user_meta( get_current_user_id(), 'rocket_boxes', [ 'rocket_warning_advanced_cache_permissions' ] );
		}

		// Run it.
		$advanced_cache = new AdvancedCache( WP_ROCKET_PLUGIN_ROOT . 'views/cache/', $this->filesystem );

		if ( empty( $expected ) ) {
			$this->markTestSkipped( 'Test doest not perform assertion, need to revisit' );

			$advanced_cache->notice_permissions();

			return;
		}

		ob_start();
		$advanced_cache->notice_permissions();
		$actual = ob_get_clean();
		if ( ! empty( $actual ) ) {
			$actual = $this->format_the_html( $actual );
		}

		$this->assertSame(
			$this->format_the_html( $expected ),
			$actual
		);
	}
}
