<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\Optimization\Minify\AdminSubscriber;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\AdminSubscriber::clean_minify_all
 * @uses   ::rocket_clean_minify
 * @uses   ::rocket_direct_filesystem
 *
 * @group  Optimize
 * @group  Minify
 * @group  AdminSubscriber
 */
class Test_RocketCleanMinifyAll extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/AdminSubscriber/cleanMinifyAll.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testCleanMinifyAll( $option ) {
        
        $options    = Mockery::mock( Options_Data::class );
        $subcriber = new AdminSubscriber( $options );

        Functions\expect( 'get_current_blog_id' )->andReturn( 1 );

        $options->shouldReceive( 'get' )
        ->once()
        ->with( 'minify_js', 0 )
        ->andReturn($option['minify_js']);

        if( $option['minify_js'] == 1 ){
            $options->shouldReceive( 'get' )->never();
        }
        else{
            $options->shouldReceive( 'get' )
            ->once()
            ->with( 'minify_css', 0 )
            ->andReturn($option['minify_css']);
        }

        if( $option['minify_css'] == 1 || $option['minify_js'] == 1 ) {
            Functions\expect( 'rocket_clean_minify' )->once();
        }
        else{
            Functions\expect( 'rocket_clean_minify' )->never();
        }

        $subcriber->clean_minify_all();
	}
}
