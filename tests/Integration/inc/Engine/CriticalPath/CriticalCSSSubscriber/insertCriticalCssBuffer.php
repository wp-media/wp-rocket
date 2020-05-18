<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::insert_critical_css_buffer
 * @group  CriticalPath
 * @group  vfs
 */
class Test_InsertCriticalCssBuffer extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/insertCriticalCssBuffer.php';

	private static $fallback_css;
	private static $user_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id   = $factory->user->create(
			[
				'role'          => 'adminstrator',
				'user_nicename' => 'rocket_tester',
			]
		);
	}

	public function tearDown() {
		parent::tearDown();

		$this->reset_post_types();
		$this->reset_taxonomies();
	}

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldDoExpected( $config, $expected_file, $fallback = null, $js_script = null ) {
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'return_1' ] );

		set_current_screen( 'front' );
		wp_set_current_user( self::$user_id );

		if ( 'front_page' === $config['type'] ) {
			$this->go_to( home_url() );
		} elseif ( 'home' === $config['type'] ) {
			update_option( 'show_on_front', ( isset( $config['show_on_front'] ) ? $config['show_on_front'] : 'page' ) );
			$home = untrailingslashit( get_option( 'home' ) );
			$this->go_to( $home );

			$this->assertTrue( is_home() );
		} elseif ( 'is_category' === $config['type'] ) {
			$id_category = self::factory()->category->create();
			$this->go_to( "/?cat=$id_category" );

			$this->assertTrue( is_category() );
		} elseif ( 'is_tag' === $config['type'] ) {
			$term_id = self::factory()->term->create(
				array(
					'name'     => 'TagName',
					'taxonomy' => 'post_tag',
				)
			);
			$this->go_to( "/?tag=TagName" );

			$this->assertTrue( is_tag() );
		} elseif ( 'is_tax' === $config['type'] ) {
			if ( ! isset( $config['taxonomy'] ) ) {
				$config['taxonomy'] = 'wptests_tax';
			}

			register_taxonomy(
				$config['taxonomy'],
				'post',
				array(
					'public' => true,
				)
			);

			$this->assertContains( $config['taxonomy'], get_taxonomies( array( 'publicly_queryable' => true ) ) );

			$t = self::factory()->term->create_and_get(
				array(
					'taxonomy' => $config['taxonomy'],
				)
			);

			$p = self::factory()->post->create();
			wp_set_object_terms( $p, $t->slug, $config['taxonomy'] );

			$this->go_to( '/?' .$config['taxonomy'] . '=' . $t->slug );

			$this->assertTrue( is_tax() );
		} elseif ( 'is_page' === $config['type'] || 'is_post' === $config['type'] ) {
			$post = self::factory()->post->create_and_get(
				[
					'import_id'   => ( isset( $config['post_id'] ) ? $config['post_id'] : 1 ),
					'post_title'  => 'Test',
					'content'     => '',
					'post_status' => 'publish',
					'post_type'   => ( 'is_page' === $config['type'] ? 'page' : 'post' )
				]
			);
			$this->go_to( get_permalink( $post ) );
			$this->assertTrue( is_singular() );
		}

		if ( isset( $fallback ) && ! empty( $config[ 'fallback_css' ] ) ) {
			self::$fallback_css = $config[ 'fallback_css' ];
			add_filter( 'pre_get_rocket_option_critical_css', [ $this, 'getFallbackCss' ] );
		}

		$html = apply_filters( 'rocket_buffer', '<html><head><title></title></head><body></body></html>' );

		if ( ! empty( $expected_file ) ) {
			$this->assertGreaterThan( 0, strpos( $html, $this->filesystem->get_contents( $expected_file ) ) );
		}

		if ( isset( $fallback ) && ! empty( $config[ 'fallback_css' ] ) ) {
			$this->assertGreaterThan( 0, strpos( $html, $config[ 'fallback_css' ] ) );
		}

		if ( isset( $js_script ) && ! empty( $js_script ) ) {
			$this->assertGreaterThan( 0, strpos( $html, $js_script ) );
		}

		if ( isset( $fallback ) && ! empty( $config[ 'fallback_css' ] ) ) {
			remove_filter( 'pre_get_rocket_option_critical_css', [ $this, 'getFallbackCss' ] );
		}

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'return_1' ] );
	}

	public function getFallbackCss() {
		return self::$fallback_css;
	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}
}
