<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\WPCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\WPCache::notice_wp_config_permissions
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_notice_html
 *
 * @group  AdminOnly
 * @group  WPCache
 */
class Test_NoticeWpConfigPermissions extends FilesystemTestCase {
    use CapTrait;

	protected $path_to_test_data = '/inc/Engine/Cache/WPCache/noticeWpConfigPermissions.php';

	private static $user_id;

	public static function set_up_before_class() {
		parent::set_up_before_class();

        self::hasAdminCapBeforeClass();
        self::setAdminCap();
		self::$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
    }

    public static function tear_down_after_class() {
        self::resetAdminCap();
    }

	public function set_up() {
		parent::set_up();

		Functions\when( 'wp_create_nonce' )->justReturn( '123456' );
	}

	public function tear_down() {
        delete_user_meta( get_current_user_id(), 'rocket_boxes', [ 'rocket_warning_wp_config_permissions' ] );

		$this->filesystem->put_contents( 'wp-config.php', "<?php\ndefine( 'DB_NAME', 'local' );\ndefine( 'DB_USER', 'root' );\n" );

		remove_filter( 'rocket_set_wp_cache_constant', [ $this, 'return_false'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEchoNotice( $config, $expected ) {
        $this->wp_cache_constant = $config['constant'];

		if ( $config['cap'] ) {
			wp_set_current_user( self::$user_id );
        }

		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );

		if ( ! $config['writable'] ) {
			$this->filesystem->chmod( 'wp-config.php', 0444 );
		}

		if ( $config['constant'] ) {
			$this->filesystem->put_contents( 'wp-config.php', "define( 'WP_CACHE', true );" );
		}

		if ( $config['boxes'] ) {
			add_user_meta( get_current_user_id(), 'rocket_boxes', [ 'rocket_warning_wp_config_permissions' ] );
		}

		if ( isset( $config['filter'] ) ) {
			add_filter( 'rocket_set_wp_cache_constant', [ $this, 'return_false'] );
		}

		// Run it.
		$wp_cache = new WPCache( $this->filesystem );

		if ( empty( $expected ) ) {
			$this->assertSame( $expected, $wp_cache->notice_wp_config_permissions() );

			return;
		}

		ob_start();
		$wp_cache->notice_wp_config_permissions();
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
