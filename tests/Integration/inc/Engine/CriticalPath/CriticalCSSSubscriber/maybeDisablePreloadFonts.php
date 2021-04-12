<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\ContentTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::maybe_disable_preload_fonts
 *
 * @group CriticalPath
 * @group vfs
 */
class Test_MaybeDisablePreloadFonts extends FilesystemTestCase {
    use ContentTrait;

    protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/maybeDisablePreloadFonts.php';
    private $async_css;
    private $critical_css;
    private $post;

    public function tearDown() : void {
        remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );
        remove_filter( 'pre_get_rocket_option_critical_css', [ $this, 'critical_css' ] );

        if ( isset( $this->post->ID) ) {
            delete_post_meta( $this->post->ID, '_rocket_exclude_async_css', 1, true );
        }

        parent::tearDown();
    }

    /**
	 * @dataProvider providerTestData
	 */
    public function testShouldReturnExpected( $config, $expected ) {
        $this->post = $this->goToContentType( $config );

        $this->donotrocketoptimize = $config['DONOTROCKETOPTIMIZE'];

        $this->async_css    = $config['options']['async_css'];
        $this->critical_css = $config['options']['critical_css'];

        add_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );
        add_filter( 'pre_get_rocket_option_critical_css', [ $this, 'critical_css' ] );

        if ( $config['is_rocket_post_excluded_option'] ) {
            add_post_meta( $this->post->ID, '_rocket_exclude_async_css', 1, true );
        }

        $value = apply_filters( 'rocket_disable_preload_fonts', false );

        if ( $expected ) {
            $this->assertTrue( $value );
        } else {
            $this->assertFalse( $value );
        }
    }

    public function async_css() {
        return $this->async_css;
    }

    public function critical_css() {
        return $this->critical_css;
    }
}
