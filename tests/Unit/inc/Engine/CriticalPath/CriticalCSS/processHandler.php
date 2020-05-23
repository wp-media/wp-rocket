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
	/**
	 * @var CriticalCSS
	 */
	private $critical_css;
	private $items_count = 1;

	public function setUp() {
		parent::setUp();
		Functions\when( 'home_url' )->justReturn( 'http://example.org/' );
		Functions\expect( 'get_current_blog_id' )->andReturn( 1 );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldHandleProcess( $config, $expected ) {

		$version         = isset( $config['version'] )         ? $config['version']         : 'default';
		$process_running = isset( $config['process_running'] ) ? $config['process_running'] : false;

		$this->assertFilters( $config['filters'] );

		if($config['filters']['do_rocket_critical_css_generation']){
			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_critical_css_generation_process_running' )
				->andReturn( $process_running );
		}

		$process = Mockery::mock( CriticalCSSGeneration::class );
		$this->critical_css = new CriticalCSS(
			$process,
			Mockery::mock( Options_Data::class ),
			$this->filesystem
		);

		if( $config['filters']['do_rocket_critical_css_generation'] && ! $process_running){
			global $wpdb;
			$wpdb = Mockery::mock( wpdb::class );

			$this->assertStopGeneration( $process );

			$this->assertSetItems( $config );

			Functions\expect( 'set_transient' )->once()->andReturn( null );
			$process
				->shouldReceive( 'push_to_queue' )->times( $this->items_count )->andReturn( null )
				->shouldReceive( 'save' )->once()->andReturn( $process )
				->shouldReceive( 'dispatch' )->once()->andReturn( null );

		}

		$this->critical_css->process_handler( $version );

		$this->assertCount( $this->items_count, $this->critical_css->items );
	}

	private function assertFilters( $filters ) {
		foreach ( (array) $filters as $filter => $return ) {
			Filters\expectApplied($filter)
				->once()
				->andReturn( $return );
		}
	}

	private function assertStopGeneration( $process )
	{
		$process->shouldReceive( 'cancel_process' )->once()->andReturn( null );
	}

	private function assertSetItems( $config ) {
		$this->assertFrontPage( $config );

		$this->assertPosts( $config );

		$this->assertTaxonomies( $config );
	}

	private function assertFrontPage( $config ) {
		Functions\expect( 'get_option' )->andReturnUsing( function ($option_key) use ($config) {
			if(isset($config[$option_key])){
				return $config[$option_key];
			}
			return null;
		} );

		if( 'page' === $config['show_on_front'] && ! empty( $config['page_for_posts'] ) ) {
			Functions\expect( 'get_permalink' )->with( $config['page_for_posts'] )->andReturn( $config['page_for_posts_url'] );
			$this->items_count++;
		}
	}

	private function assertPosts($config)
	{
		global $wpdb;

		Functions\expect( 'get_post_types' )
			->with( [
				'public'             => true,
				'publicly_queryable' => true,
			] )->once()->andReturn( $config['post_types'] );

		Functions\expect( 'esc_sql' )->andReturnFirstArg();

		$wpdb->posts = 'posts';
		$wpdb->shouldReceive('get_results')->with("
		    SELECT MAX(ID) as ID, post_type
		    FROM (
		        SELECT ID, post_type
		        FROM {$wpdb->posts}
				WHERE post_type IN ( '".implode("','", $config['post_types'])."','page' )
		        AND post_status = 'publish'
		        ORDER BY post_date DESC
		    ) AS posts
		    GROUP BY post_type")->once()->andReturn( $config['posts'] );

		foreach ( (array) $config['posts'] as $post ) {
			Functions\expect( 'get_permalink' )->with( $post->ID )->andReturn( $post->post_url );
			$this->items_count++;
		}
	}

	private function assertTaxonomies($config)
	{
		global $wpdb;

		Functions\expect( 'get_taxonomies' )
			->with( [
				'public'             => true,
				'publicly_queryable' => true,
			] )->once()->andReturn( $config['taxonomies'] );

		$wpdb->term_taxonomy = 'terms';
		$wpdb->shouldReceive('get_results')->with("SELECT MAX( term_id ) AS ID, taxonomy
			FROM (
				SELECT term_id, taxonomy
				FROM $wpdb->term_taxonomy
				WHERE taxonomy IN ( '".implode("','", $config['taxonomies'])."' )
				AND count > 0
			) AS taxonomies
			GROUP BY taxonomy")->once()->andReturn( $config['terms'] );

		foreach ($config['terms'] as $term) {
			Functions\expect( 'get_term_link' )->with( $term->ID )->andReturn( $term->url );
			$this->items_count++;
		}
	}
}
