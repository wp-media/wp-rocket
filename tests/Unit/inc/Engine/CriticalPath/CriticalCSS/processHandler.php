<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSS;

use Brain\Monkey\{Filters, Functions};
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use wpdb;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSS::process_handler
 *
 * @group CriticalCss
 * @group CriticalPath
 */
class TestProcessHandler extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/processHandler.php';

	private $critical_css;
	private $expected_items = [];
	private $wpdb;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/wpdb.php';
	}

	protected function setUp(): void {
		parent::setUp();

		$this->expected_items = [
			'front_page' => [
				'type'  => 'front_page',
				'url'   => 'http://example.org/',
				'path'  => 'front_page.css',
				'check' => 0,
			],
		];

		Functions\when( 'home_url' )->justReturn( 'http://example.org/' );
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->wpdb = new wpdb( 'dbuser', 'dbpassword', 'dbname', 'dbhost' );

		$GLOBALS['wpdb'] = $this->wpdb;
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wpdb'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldHandleProcess( $config, $expected ) {
		$version         = isset( $config['version'] ) ? $config['version'] : 'default';
		$process_running = isset( $config['process_running'] ) ? $config['process_running'] : false;

		$this->assertFilters( $config['filters'] );

		if ( $config['filters']['do_rocket_critical_css_generation'] ) {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_critical_css_generation_process_running' )
				->andReturn( $process_running );
		}

		$process            = Mockery::mock( CriticalCSSGeneration::class );
		$this->critical_css = new CriticalCSS(
			$process,
			Mockery::mock( Options_Data::class ),
			$this->filesystem
		);

		if ( $expected['generated'] ) {
			$this->assertStopGeneration( $process );

			$this->assertSetItems( $config );

			Functions\expect( 'set_transient' )->once()->andReturn( null );
			$number_items = count( $this->expected_items );
			$process
				->shouldReceive( 'push_to_queue' )->times( $number_items )->andReturn( null );
			$process
				->shouldReceive( 'save' )->once()->andReturn( $process );
			$process
				->shouldReceive( 'dispatch' )->once()->andReturn( null );

		}

		$this->critical_css->process_handler( $version );

		// Check the expected items were built.
		$this->assertEquals( $expected['items_count'], count( $this->expected_items ) );

		foreach ( $this->critical_css->items as $type => $details ) {
			$this->assertArrayHasKey( $type, $this->expected_items );
			$this->assertSame( $this->expected_items[ $type ], $details );
		}
	}

	private function assertFilters( $filters ) {
		foreach ( (array) $filters as $filter => $return ) {
			Filters\expectApplied( $filter )
				->once()
				->andReturn( $return );
		}
	}

	private function assertStopGeneration( $process ) {
		$process->shouldReceive( 'cancel_process' )->once()->andReturn( null );
	}

	private function assertSetItems( $config ) {
		$this->assertFrontPage( $config );

		$this->assertPosts( $config );

		$this->assertTaxonomies( $config );
	}

	private function assertFrontPage( $config ) {
		Functions\expect( 'get_option' )
			->andReturnUsing(
				function ( $option_key ) use ( $config ) {
					if ( isset( $config[ $option_key ] ) ) {
						return $config[ $option_key ];
					}

					return null;
				}
			);

		if (
			'page' === $config['show_on_front']
			&&
			! empty( $config['page_for_posts'] )
		) {
			Functions\expect( 'get_permalink' )
				->once()
				->with( $config['page_for_posts'] )
				->andReturn( $config['page_for_posts_url'] );

			$this->expected_items['home'] = [
				'type'  => 'home',
				'url'   => $config['page_for_posts_url'],
				'path'  => 'home.css',
				'check' => 0,
			];
		}
	}

	private function assertPosts( $config ) {
		Functions\expect( 'get_post_types' )
			->once()
			->with( [
					'public'             => true,
					'publicly_queryable' => true,
				]
			)
			->andReturn( $config['post_types'] );

		Functions\expect( 'esc_sql' )->andReturnFirstArg();

		$this->wpdb->setPosts( $config['posts'] );

		foreach ( $config['posts'] as $post ) {
			Functions\expect( 'get_permalink' )
				->with( $post->ID )
				->andReturn( $post->post_url );

			$this->expected_items[ $post->post_type ] = [
				'type'  => $post->post_type,
				'url'   => $post->post_url,
				'path'  => "{$post->post_type}.css",
				'check' => 0,
			];
		}
	}

	private function assertTaxonomies( $config ) {
		Functions\expect( 'get_taxonomies' )
			->once()
			->with(
				[
					'public'             => true,
					'publicly_queryable' => true,
				]
			)
			->andReturn( $config['taxonomies'] );

		$this->wpdb->setTerms( $config['terms'] );

		foreach ( $config['terms'] as $term ) {
			Functions\expect( 'get_term_link' )
				->with( $term->ID, $term->taxonomy )
				->andReturn( $term->url );

			$this->expected_items[ $term->taxonomy ] = [
				'type'  => $term->taxonomy,
				'url'   => $term->url,
				'path'  => "{$term->taxonomy}.css",
				'check' => 0,
			];
		}
	}
}
