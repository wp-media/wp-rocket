<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSS;

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSS::process_handler
 *
 * @group  CriticalCss
 */
class Test_ProcessHandler extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/processHandler.php';

	public function setUp() {
		parent::setUp();
		Functions\when( 'home_url' )->justReturn( 'http://example.org/' );
		Functions\expect( 'get_current_blog_id' )->andReturn( 1 );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDeleteFilesFromRootFolder( $config, $expected ) {

		$version = isset( $config['version'] ) ? $config['version'] : 'default';
		$process_running = isset( $config['process_running'] ) ? $config['process_running'] : false;

		$critical_css_path = "vfs://public/wp-content/cache/critical-css/1/";

		foreach ( (array) $config['filters'] as $filter => $return ) {
			Filters\expectApplied($filter)
				->once()
				->andReturn( $return );
		}

		if($config['filters']['do_rocket_critical_css_generation']){
			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_critical_css_generation_process_running' )
				->andReturn( $process_running );
		}

		$process = Mockery::mock( CriticalCSSGeneration::class );

		if( $config['filters']['do_rocket_critical_css_generation'] && ! $process_running){
			$process->shouldReceive( 'cancel_process' )->andReturn( null );

			Functions\expect( 'get_option' )
				->with('page_for_posts')->andReturn( $config['page_for_posts'] )
				->with('show_on_front')->andReturn( $config['show_on_front'] );

			Functions\expect( 'get_permalink' )->with( $config['page_for_posts'] )->andReturn( $config['page_for_posts_url'] );
			Functions\expect( 'get_post_types' )
				->with( [
					'public'             => true,
					'publicly_queryable' => true,
				] )
				->andReturn( $config['post_types'] );

			Functions\expect( 'esc_sql' )->andReturnFirstArg();

		}

		$critical_css = new CriticalCSS(
			$process,
			Mockery::mock( Options_Data::class ),
			$this->filesystem
		);
		$critical_css->process_handler( $version );
	}
}
