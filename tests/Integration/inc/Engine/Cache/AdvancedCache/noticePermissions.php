<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers WP_Rocket\Engine\Cache\AdvancedCache::notice_permissions
 *
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_notice_html
 * @uses   ::rocket_direct_filesystem
 *
 * @group  AdvancedCache
 * @group  AdminOnly
 */
class Test_NoticePermissions extends FilesystemTestCase {
    protected $path_to_test_data = '/inc/Engine/Cache/AdvancedCache/noticePermissions.php';
    private static $advanced_cache;

    public static function setUpBeforeClass() {
		$container            = apply_filters( 'rocket_container', null );
		self::$advanced_cache = $container->get( 'advanced_cache' );
    }

    public function tearDown() {
        $this->filesystem->chmod( 'wp-content/advanced-cache.php', 0644 );
        delete_user_meta( get_current_user_id(), 'rocket_boxes', [ 'rocket_warning_advanced_cache_permissions' ] );

        parent::tearDown();
    }

    private function getActualHtml() {
        ob_start();

        self::$advanced_cache->notice_permissions();
        return $this->format_the_html( ob_get_clean() );
    }

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEchoNotice( $config, $expected ) {
        if ( $config['cap'] ) {
            $admin = get_role( 'administrator' );
            $admin->add_cap( 'rocket_manage_options' );

            $user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
            wp_set_current_user( $user_id );
        }

        Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );

        if ( ! $config['writable'] ) {
            $this->filesystem->chmod( 'wp-content/advanced-cache.php', 0444 );
        }

        if ( $config['boxes'] ) {
            add_user_meta( $user_id, 'rocket_boxes', [ 'rocket_warning_advanced_cache_permissions' ] );
        }

        if ( ! empty( $expected ) ) {
            $this->assertSame(
                $this->format_the_html( $expected ),
                $this->getActualHtml()
            );
        } else {
            $this->assertSame( $expected, self::$advanced_cache->notice_permissions() );
        }
    }
}
