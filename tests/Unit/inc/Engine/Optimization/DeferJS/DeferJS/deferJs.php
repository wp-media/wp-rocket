<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DeferJS\DeferJS;

use Brain\Monkey\Functions;
use Mockery;
use stdClass;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DeferJS\DeferJS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DeferJS\DeferJS::defer_js
 *
 * @group  DeferJS
 */
class Test_DeferJs extends TestCase {
    /**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
        $this->donotrocketoptimize = $config['donotrocketoptimize'];

        $options  = Mockery::mock( Options_Data::class );
        $defer_js = new DeferJS( $options );

        $options->shouldReceive( 'get' )
            ->atMost()
            ->twice()
            ->with( 'defer_all_js', 0 )
            ->andReturn( $config['options']['defer_all_js'] );
        
        $options->shouldReceive( 'get' )
            ->atMost()
            ->once()
            ->with( 'defer_all_js_safe', 0 )
            ->andReturn( $config['options']['defer_all_js_safe'] );
        
        $options->shouldReceive( 'get' )
            ->atMost()
            ->once()
            ->with( 'exclude_defer_js', [] )
            ->andReturn( $config['options']['exclude_defer_js'] );

        Functions\when( 'is_rocket_post_excluded_option' )
            ->justReturn( $config['post_meta'] );
        
        Functions\when( 'site_url' )->alias( function( $path = '') {
            return 'http://example.org/' . ltrim( $path, '/' );
        } );
        Functions\when( 'rocket_clean_exclude_file' )->alias( function( $file = '' ) {
            return parse_url( $file, PHP_URL_PATH );
        } );
        Functions\when( 'wp_scripts' )->alias( function() {
            $wp_scripts = new stdClass();
            $jquery     = new stdClass();
            $jquery->src = '/wp-includes/js/jquery/jquery.js';
            $wp_scripts->registered = [
                'jquery-core' => $jquery,
            ];

            return $wp_scripts;
        } );

        $this->assertSame(
            $expected,
            $defer_js->defer_js( $html )
        );
    }
}
