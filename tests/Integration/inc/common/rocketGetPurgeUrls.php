<?php
namespace WP_Rocket\Tests\Integration\inc\common;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers ::rocket_clean_cache_theme_update
 * @uses   ::rocket_clean_domain
 *
 * @group  Common
 * @group  Purge
 */
class Test_RocketGetPurgeUrls extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/common/rocketGetPurgeUrls.php';

	private $site_options = [
		'stylesheet' => ''
	];

	private $page_for_posts;
	private $posts_map   = [];
	private $authors_map = [];

	public function setUp() {
		$this->set_permalink_structure( "/%postname%/" );
		parent::setUp();

		$k = 1;
		foreach ( $this->config['urls']['posts'] as $post_id => $url ) {
			$now = time();
			$post_name = str_replace("http://example.org/", "", $url);
			$post_details = [
				'post_date' => gmdate( 'Y-m-d H:i:s', $now - $k ),
				'post_title' => !empty( $post_name ) ? $post_name : "home",
				'post_type' => 20 !== $post_id ? 'post' : 'page'
			];

			if ( 4 === $post_id ) {
				$post_details['post_parent'] = $this->posts_map[3];
			}

			$new_id = $this->factory->post->create($post_details);
			$this->posts_map[$post_id] = $new_id;
			if ( 20 === $post_id ) {
				$this->page_for_posts = $new_id;
			}
			$k++;
		}

		foreach ( $this->config['urls']['authors'] as $author_id => $url ) {
			$this->authors_map[$author_id] = $this->factory->user->create( [ 'user_login' => str_replace("http://example.org/author/", "", $url), 'role' => 'editor' ] );
		}

		Functions\expect( 'get_rocket_option' )->withAnyArgs()->andReturnUsing( function( $option_name, $default=null ) {
			return isset( $this->site_options[$option_name] ) ? $this->site_options[$option_name] : $default;
		} );

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';
	}

	public function tearDown() {
		parent::tearDown();
		foreach ( $this->site_options as $option_name => $option_value ) {
			remove_filter( 'pre_option_'.$option_name, [$this, 'prepare_option'], 10, 3 );
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnUrls( $config, $expected ) {
		global $post;

		// Create the custom post firstly.
		if ( ! post_type_exists( $config['post_data']['post_type'] ) ){
			register_post_type( $config['post_data']['post_type'] );
		}

		$post_data = [
			'post_title' => $config['post_data']['post_name'],
			'post_name' => $config['post_data']['post_name'],
			'post_type' => $config['post_data']['post_type'],
			'post_author' => $this->authors_map[$config['post_data']['post_author']],
		];

		if ( isset( $config['post_data']['ancestors'] ) ){
			$post_data['post_parent'] = $this->posts_map[4];
		}

		$post_id = $this->factory->post->create($post_data);
		$post    = get_post($post_id);
		$options = isset( $config['options'] ) ? $config['options'] : [];

		if ( isset( $options['page_for_posts'] ) ) {
			$options['page_for_posts'] = $this->page_for_posts;
		}

		$this->site_options = array_merge($this->site_options, $options);

		foreach ( $this->site_options as $option_name => $option_value ) {
			add_filter( 'pre_option_'.$option_name, [$this, 'prepare_option'], 10, 3 );
		}

		if( 'post' !== $post->post_type ){
			$archive_url = $this->get_archive_url( $post->post_type );
			Functions\expect('get_post_type_archive_link')
				->once()
				->with( $post->post_type )
				->andReturn( $archive_url );
		}


		$actual = rocket_get_purge_urls( $post_id, $post );
		asort($expected);
		asort($actual);
		$this->assertEquals( array_values( $expected ) , array_values( $actual ) );
	}

	private function get_post_url( $post_id ) {
		return isset( $this->config['urls']['posts'][$post_id] ) ? $this->config['urls']['posts'][$post_id] : '';
	}

	private function get_author_url( $author_id ) {
		return isset( $this->config['urls']['authors'][$author_id] ) ? $this->config['urls']['authors'][$author_id] : '';
	}

	private function get_archive_url( $post_type ) {
		return isset( $this->config['urls']['archives'][$post_type] ) ? $this->config['urls']['archives'][$post_type] : '';
	}

	public function prepare_option( $pre_option, $option, $default ) {
		return isset( $this->site_options[$option] ) ? $this->site_options[$option] : $default;
	}

}
