<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSS;

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
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
	private $to_be_removed = [
		'filters'    => [],
		'transients' => [],
		'post_types' => [],
		'posts'      => [],
		'taxonomies' => [],
		'terms' => [],
	];

	public function tearDown()
	{
		parent::tearDown();

		foreach ($this->to_be_removed as $item_name => $item) {
			switch ( $item_name ) {
				case 'filters':
					foreach ($item as $filter => $callback) {
						remove_filter($filter, $callback);
					}
					break;

				case 'transients':
					foreach ($item as $transient_key) {
						delete_transient($transient_key);
					}
					break;

				case 'post_types':
					foreach ($item as $post_type) {
						unregister_post_type($post_type);
					}
					break;
				case 'posts':
					foreach ($item as $post_id) {
						wp_delete_post( $post_id, true );
					}
					break;
				case 'taxonomies':
					foreach ($item as $taxonomy) {
						unregister_taxonomy( $taxonomy );
					}
					break;
				case 'terms':
					foreach ($item as $term_id => $taxonomy) {
						wp_delete_term( $taxonomy, $term_id );
					}
					break;
			}
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldHandleProcess( $config, $expected ) {

		$version         = isset( $config['version'] )         ? $config['version']         : 'default';
		$process_running = isset( $config['process_running'] ) ? $config['process_running'] : false;
		$page_for_posts  = isset( $config['page_for_posts'] )  ? $config['page_for_posts']  : null;
		$show_on_front   = isset( $config['show_on_front'] )   ? $config['show_on_front']   : null;

		$this->addFilters( $config['filters'] );

		if($config['filters']['do_rocket_critical_css_generation']){
			set_transient('rocket_critical_css_generation_process_running', $process_running);
			$this->to_be_removed['transients'][] = 'rocket_critical_css_generation_process_running';
		}

		$data_manager = new DataManager($this->config['vfs_dir'], $this->filesystem);
		$api_client   = new APIClient();
		$processor_service = new ProcessorService($data_manager, $api_client);
		$process = new CriticalCSSGeneration($processor_service);
		$options = new Options_Data( [
			'page_for_posts' => $page_for_posts,
			'show_on_front' => $show_on_front,
		] );
		$this->critical_css = new CriticalCSS(
			$process,
			$options,
			$this->filesystem
		);

		if( $expected['generated'] ){
			$this->assertStopGeneration( $process );

			$this->prepareSetItems( $config );
		}

		$this->critical_css->process_handler( $version );

		$this->assertCount( $expected['items_count'], $this->critical_css->items );
	}

	private function addFilters( $filters ) {
		foreach ( (array) $filters as $filter => $return ) {
			add_filter($filter, function() use ($return) { return $return; });
			$this->to_be_removed['filters'][] = [
				$filter => function(){}
			];
		}
	}

	private function assertStopGeneration( $process )
	{
		//$process->shouldReceive( 'cancel_process' )->once()->andReturn( null );
	}

	private function prepareSetItems( $config ) {
		$this->prepareFrontPage( $config );

		$this->preparePosts( $config );

		$this->prepareTaxonomies( $config );
	}

	private function prepareFrontPage( $config ) {
		if( 'page' === $config['show_on_front'] && ! empty( $config['page_for_posts'] ) ) {
			$this->items_count++;
		}
	}

	private function preparePosts($config)
	{
		//create those fixture post types
		if( !empty( $config['post_types'] ) ){
			foreach ( (array) $config['post_types'] as $post_type ) {
				if( !post_type_exists( $post_type ) ) {
					//create this post type
					$args = array(
						'public'    => true,
						'publicly_queryable' => true,
						'label'     => $post_type
					);
					register_post_type( $post_type, $args );

					$this->to_be_removed['post_types'][] = $post_type;
				}
			}
		}

		//create posts
		foreach ( (array) $config['posts'] as $post ) {
			$my_post = array(
				'post_title'    => 'Test title',
				'post_content'  => 'Test Content.',
				'post_status'   => $post->post_status,
			);
			$post_id = wp_insert_post( $my_post );

			$this->to_be_removed['posts'][] = $post_id;

			$this->items_count++;
		}
	}

	private function prepareTaxonomies($config)
	{
		//create those fixture taxonomies
		if( !empty( $config['taxonomies'] ) ){
			foreach ( (array) $config['taxonomies'] as $taxonomy ) {
				if( !taxonomy_exists( $taxonomy ) ) {
					//create this taxonomy
					register_taxonomy( $taxonomy, 'post' );

					$this->to_be_removed['taxonomies'][] = $taxonomy;
				}
			}
		}

		//create terms
		foreach ( (array) $config['terms'] as $term ) {
			$term_created = wp_insert_term(
				'Test Term',
				$term->taxonomy
			);

			$this->to_be_removed['terms'][] = $term_created['term_id'];

			$this->items_count++;
		}
	}
}
