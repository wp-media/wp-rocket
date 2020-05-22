<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSS;

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use wpdb;

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

			global $wpdb;
			$wpdb = Mockery::mock( wpdb::class );

			$wpdb->shouldReceive('get_results')->with("
		    SELECT MAX(ID) as ID, post_type
		    FROM (
		        SELECT ID, post_type
		        FROM posts
				WHERE post_type IN ( '".implode("','", $config['post_types'])."','page' )
		        AND post_status = 'publish'
		        ORDER BY post_date DESC
		    ) AS posts
		    GROUP BY post_type")->andReturn( $config['posts'] );
			$wpdb->posts = 'posts';
			$wpdb->term_taxonomy = 'terms';

			Functions\expect( 'get_taxonomies' )
				->with( [
					'public'             => true,
					'publicly_queryable' => true,
				] )
				->andReturn( $config['taxonomies'] );

			$wpdb->shouldReceive('get_results')->with("SELECT MAX( term_id ) AS ID, taxonomy
			FROM (
				SELECT term_id, taxonomy
				FROM $wpdb->term_taxonomy
				WHERE taxonomy IN ( '".implode("','", $config['taxonomies'])."' )
				AND count > 0
			) AS taxonomies
			GROUP BY taxonomy")->andReturn( $config['terms'] );

			foreach ($config['posts'] as $post) {
				Functions\expect( 'get_permalink' )->with( $post->ID )->andReturn( $post->post_url );
			}

			foreach ($config['terms'] as $term) {
				Functions\expect( 'get_term_link' )->with( $term->ID )->andReturn( $term->url );
			}

			$process->shouldReceive( 'push_to_queue' )->andReturn( null );

			Functions\expect( 'set_transient' )->andReturn( null );
			$process->shouldReceive( 'save' )->andReturn( $process );
			$process->shouldReceive( 'dispatch' )->andReturn( null );

		}

		$critical_css = new CriticalCSS(
			$process,
			Mockery::mock( Options_Data::class ),
			$this->filesystem
		);
		$critical_css->process_handler( $version );
	}
}
