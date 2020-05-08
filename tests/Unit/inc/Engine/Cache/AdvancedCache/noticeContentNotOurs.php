<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers WP_Rocket\Engine\Cache\AdvancedCache::notice_content_not_ours
 *
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_notice_html
 *
 * @group  AdvancedCache
 */
class Test_NoticeContentNotOurs extends FilesystemTestCase {
    protected $path_to_test_data = '/inc/Engine/Cache/AdvancedCache/noticeContentNotOurs.php';
    private $advanced_cache;

	public function setUp() {
        parent::setUp();

        Functions\expect( 'rocket_get_constant' )
            ->once()
            ->with( 'WP_CONTENT_DIR' )
            ->andReturn(
                $this->filesystem->getUrl( 'wp-content' )
            );

		$this->advanced_cache = new AdvancedCache(
            $this->filesystem->getUrl( 'wp-content/plugins/wp-rocket/views/cache' )
        );
    }

    public function tearDown() {
        unset( $GLOBALS['pagenow'] );
        unset( $_GET['activate'] );

        parent::tearDown();
    }

    private function getActualHtml() {
        ob_start();

        $this->advanced_cache->notice_content_not_ours();
        return $this->format_the_html( ob_get_clean() );
    }

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEchoNotice( $config, $expected ) {
        $GLOBALS['pagenow'] = $config['pagenow'];
        $_GET['activate']   = $config['activate'];
        Functions\when( 'current_user_can' )->justReturn( $config['cap'] );
        Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );

        Functions\expect( 'rocket_get_constant' )
            ->atMost()
            ->times( 1 )
            ->with( 'WP_ROCKET_ADVANCED_CACHE' )
            ->andReturn( $config['constant'] );

        Functions\when( 'rocket_notice_writing_permissions' )->justReturn( $config['message'] );
        Functions\when( 'rocket_notice_html' )->alias( function() use ( $expected ) {
            echo $expected;
        } );

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
