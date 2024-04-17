<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSS;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\APIClient;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSS::process_handler
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration::save
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration::dispatch
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration::cancel_process
 * @uses   ::rocket_get_constant
 *
 * @group  CriticalCss
 * @group  CriticalPath
 */
class Test_ProcessHandler extends FilesystemTestCase {
	use DBTrait;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::installFresh();
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/processHandler.php';

	private $critical_css;
	private $to_be_removed;
	private $expected_items;

	public function set_up() {
		parent::set_up();

		$this->to_be_removed  = [
			'filters'    => [],
			'transients' => [],
			'post_types' => [],
			'posts'      => [],
			'taxonomies' => [],
			'terms'      => [],
		];
		$this->expected_items = [
			'front_page' => [
				'type'  => 'front_page',
				'url'   => 'http://example.org/',
				'path'  => 'front_page.css',
				'check' => 0,
			],
		];
	}

	public function tear_down() {
		parent::tear_down();

		foreach ( $this->to_be_removed as $item_name => $item ) {
			switch ( $item_name ) {
				case 'filters':
					foreach ( $item as $filter => $callback ) {
						remove_filter( $filter, $callback );
					}
					break;

				case 'transients':
					foreach ( $item as $transient_key ) {
						delete_transient( $transient_key );
					}
					break;

				case 'post_types':
					foreach ( $item as $post_type ) {
						unregister_post_type( $post_type );
					}
					break;
				case 'posts':
					foreach ( $item as $post_id ) {
						wp_delete_post( $post_id, true );
					}
					break;
				case 'taxonomies':
					foreach ( $item as $taxonomy ) {
						unregister_taxonomy( $taxonomy );
					}
					break;
				case 'terms':
					foreach ( $item as $term_id => $taxonomy ) {
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
		$version         = isset( $config['version'] ) ? $config['version'] : 'default';
		$process_running = isset( $config['process_running'] ) ? $config['process_running'] : false;
		$page_for_posts  = isset( $config['page_for_posts'] ) ? $config['page_for_posts'] : null;
		$show_on_front   = isset( $config['show_on_front'] ) ? $config['show_on_front'] : null;

		$this->addFilters( $config['filters'] );

		if ( $config['filters']['do_rocket_critical_css_generation'] ) {
			set_transient( 'rocket_critical_css_generation_process_running', $process_running );
			$this->to_be_removed['transients'][] = 'rocket_critical_css_generation_process_running';
		}

		$data_manager       = new DataManager( $this->config['vfs_dir'], $this->filesystem );
		$api_client         = new APIClient();
		$processor_service  = new ProcessorService( $data_manager, $api_client );
		$process            = new CriticalCSSGeneration( $processor_service );
		$options            = new Options_Data( [
			'page_for_posts' => $page_for_posts,
			'show_on_front'  => $show_on_front,
		] );
		$this->critical_css = new CriticalCSS(
			$process,
			$options,
			$this->filesystem
		);

		if ( $expected['generated'] ) {
			$this->assertStopGeneration( $process );

			$this->prepareSetItems( $config );
		}

		$this->critical_css->process_handler( $version );

		// Check the expected items were built.
		$this->assertEquals( $expected['items_count'], count( $this->expected_items ) );
		foreach ( $this->critical_css->items as $type => $details ) {
			$this->assertArrayHasKey( $type, $this->expected_items );

			$this->assertSame( $this->expected_items[ $type ], $details );
		}
	}

	private function addFilters( $filters ) {
		foreach ( (array) $filters as $filter => $return ) {
			add_filter( $filter, function () use ( $return ) {
				return $return;
			} );
			$this->to_be_removed['filters'][] = [
				$filter => function () {
				},
			];
		}
	}

	private function assertStopGeneration( $process ) {
		//$process->shouldReceive( 'cancel_process' )->once()->andReturn( null );
	}

	private function prepareSetItems( $config ) {
		$this->prepareFrontPage( $config );

		$this->preparePosts( $config );

		$this->prepareTaxonomies( $config );
	}

	private function prepareFrontPage( $config ) {
		if (
			'page' === $config['show_on_front']
			&&
			! empty( $config['page_for_posts'] )
		) {
			$this->expected_items['home'] = [
				'type'  => 'home',
				'url'   => $config['page_for_posts_url'],
				'path'  => 'home.css',
				'check' => 0,
			];
		}
	}

	private function preparePosts( $config ) {
		// create those fixture post types
		foreach ( (array) $config['post_types'] as $post_type ) {
			if ( ! post_type_exists( $post_type ) ) {
				// create this post type
				$args = [
					'public'             => true,
					'publicly_queryable' => true,
					'label'              => $post_type,
				];
				register_post_type( $post_type, $args );

				$this->to_be_removed['post_types'][] = $post_type;
			}
		}

		// create posts
		foreach ( (array) $config['posts'] as $post ) {
			$my_post = [
				'post_title'   => 'Test title',
				'post_content' => 'Test Content.',
				'post_status'  => $post->post_status,
			];
			$post_id = wp_insert_post( $my_post );

			$this->to_be_removed['posts'][] = $post_id;

			$this->expected_items[ $post->post_type ] = [
				'type'  => $post->post_type,
				'url'   => get_permalink( $post_id ),
				'path'  => "{$post->post_type}.css",
				'check' => 0,
			];
		}
	}

	private function prepareTaxonomies( $config ) {
		//create those fixture taxonomies
		foreach ( (array) $config['taxonomies'] as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				//create this taxonomy
				register_taxonomy( $taxonomy, 'post' );

				$this->to_be_removed['taxonomies'][] = $taxonomy;
			}
		}

		//create terms
		foreach ( (array) $config['terms'] as $term ) {
			$term_array = $this->get_term( $term );

			$this->expected_items[ $term->taxonomy ] = [
				'type'  => $term->taxonomy,
				'url'   => get_term_link( $term_array['term_id'], $term->taxonomy ),
				'path'  => "{$term->taxonomy}.css",
				'check' => 0,
			];
		}
	}

	private function get_term( $term ) {
		$term_array = get_term( $term->ID, $term->taxonomy, ARRAY_A );

		if ( is_array( $term_array ) ) {
			return $term_array;
		}

		$term_array = wp_insert_term(
			'Test Term',
			$term->taxonomy
		);

		$this->to_be_removed['terms'][$term_array['term_id']] = $term->taxonomy;

		return $term_array;
	}
}
