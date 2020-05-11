<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdvancedCache;
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
    private $advanced_cache;

    public function setUp() {
        parent::setUp();

        $this->whenRocketGetConstant();

        $this->advanced_cache = new AdvancedCache( 
            $this->filesystem->getUrl( 'wp-content/plugins/wp-rocket/views/cache' )
        );
    }

    public function tearDown() {
        $this->filesystem->chmod( 'wp-content/advanced-cache.php', 0644 );
        delete_user_meta( get_current_user_id(), 'rocket_boxes', [ 'rocket_warning_advanced_cache_permissions' ] );

        parent::tearDown();
    }

    private function getActualHtml() {
        ob_start();

        $this->advanced_cache->notice_permissions();
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

        if ( $config['constant'] ) {
            $this->filesystem->put_contents( 'wp-content/advanced-cache.php', 'WP_ROCKET_ADVANCED_CACHE' );
        }

        if ( $config['boxes'] ) {
            add_user_meta( $user_id, 'rocket_boxes', [ 'rocket_warning_advanced_cache_permissions' ] );
        }

        Functions\when( 'wp_create_nonce' )->justReturn( '123456' );

        if ( ! empty( $expected ) ) {
            $this->assertSame(
                $this->format_the_html( $expected ),
                $this->getActualHtml()
            );
        } else {
            $this->assertSame( $expected, $this->advanced_cache->notice_permissions() );
        }
    }
}
