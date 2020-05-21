<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::insert_critical_css_buffer
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_InsertCriticalCssBuffer extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/insertCriticalCssBuffer.php';

	protected static $use_settings_trait = true;
	private static   $user_id;

	private $fallback_css;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create(
			[
				'role'          => 'adminstrator',
				'user_nicename' => 'rocket_tester',
			]
		);
	}

	public function setUp() {
		parent::setUp();

		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'return_1' ] );
		wp_set_current_user( self::$user_id );
		set_current_screen( 'front' );
	}

	public function tearDown() {
		parent::tearDown();

		$this->reset_post_types();
		$this->reset_taxonomies();

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'return_1' ] );
		remove_filter( 'pre_get_rocket_option_critical_css', [ $this, 'getFallbackCss' ] );
		update_option( 'show_on_front', 'posts' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected_file, $fallback = null, $js_script = null ) {

		switch ( $config['type'] ) {
			case 'front_page':
				$this->go_to( home_url() );
				break;

			case 'home':
				$this->setUpHome( $config );
				break;

			case 'is_category':
				$this->setUpCategory( $config );
				break;

			case 'is_tag':
				$this->setUpTag( $config );
				break;

			case 'is_tax':
				$this->setUpTax( $config );
				break;

			case 'is_page':
			case 'is_post':
				$this->setUpPost( $config );
		}

		if ( isset( $fallback ) && ! empty( $config['fallback_css'] ) ) {
			$this->fallback_css = $config['fallback_css'];
			add_filter( 'pre_get_rocket_option_critical_css', [ $this, 'getFallbackCss' ] );
		}

		$html = apply_filters( 'rocket_buffer', '<html><head><title></title></head><body></body></html>' );

		if ( ! empty( $expected_file ) ) {
			$this->assertGreaterThan( 0, strpos( $html, $this->filesystem->get_contents( $expected_file ) ) );
		}

		if ( isset( $fallback ) && ! empty( $config['fallback_css'] ) ) {
			$this->assertGreaterThan( 0, strpos( $html, $config['fallback_css'] ) );
		}

		if ( isset( $js_script ) && ! empty( $js_script ) ) {
			$this->assertGreaterThan( 0, strpos( $html, $js_script ) );
		}
	}

	private function setUpHome( $config ) {
		$show_on_front = isset( $config['show_on_front'] ) ? $config['show_on_front'] : 'page';
		update_option( 'show_on_front', $show_on_front );

		set_current_screen( 'front' );
		$home = untrailingslashit( get_option( 'home' ) );
		$this->go_to( $home );

		$this->assertTrue( is_home() );
	}

	private function setUpCategory() {
		$id_category = $this->factory->category->create();
		$this->go_to( "/?cat={$id_category}" );

		$this->assertTrue( is_category() );
	}

	private function setUpTag() {
		$this->factory->term->create(
			[
				'name'     => 'TagName',
				'taxonomy' => 'post_tag',
			]
		);
		$this->go_to( "/?tag=TagName" );

		$this->assertTrue( is_tag() );
	}

	private function setUpTax( $config ) {
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

		$t = $this->factory->term->create_and_get(
			array(
				'taxonomy' => $config['taxonomy'],
			)
		);

		$p = $this->factory->post->create();
		wp_set_object_terms( $p, $t->slug, $config['taxonomy'] );

		$this->go_to( '/?' . $config['taxonomy'] . '=' . $t->slug );

		$this->assertTrue( is_tax() );
	}

	private function setUpPost( $config ) {
		$post = $this->factory->post->create_and_get(
			[
				'import_id'   => ( isset( $config['post_id'] ) ? $config['post_id'] : 1 ),
				'post_title'  => 'Test',
				'content'     => '',
				'post_status' => 'publish',
				'post_type'   => ( 'is_page' === $config['type'] ? 'page' : 'post' ),
			]
		);
		$this->go_to( get_permalink( $post ) );
		$this->assertTrue( is_singular() );
	}

	public function getFallbackCss() {
		return $this->fallback_css;
	}
}
