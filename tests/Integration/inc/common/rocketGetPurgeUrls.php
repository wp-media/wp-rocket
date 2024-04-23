<?php
namespace WP_Rocket\Tests\Integration\inc\common;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering ::rocket_get_purge_urls
 * @uses   ::rocket_clean_domain
 *
 * @group  Common
 * @group  Purge
 */
class Test_RocketGetPurgeUrls extends FilesystemTestCase {
	use DBTrait;

	protected $path_to_test_data = '/inc/common/rocketGetPurgeUrls.php';

	private $site_options = [
		'stylesheet' => ''
	];

	private $page_for_posts;
	private $posts_map   = [];
	private $authors_map = [];
	private $post_data   = [];

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installFresh();
	}

	public static function tear_down_after_class() {
		self::uninstallAll();

		parent::tear_down_after_class();
	}

	public function set_up() {
		$this->set_permalink_structure( "/%postname%/" );
		parent::set_up();

		$this->create_posts( $this->config['urls']['posts'] );

		$this->create_authors( $this->config['urls']['authors'] );

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';
	}

	public function tear_down() {
		parent::tear_down();
		foreach ( $this->site_options as $option_name => $option_value ) {
			remove_filter( 'pre_option_'.$option_name, [$this, 'prepare_option'], 10 );
		}
		remove_filter( 'get_previous_post_where', [$this, 'get_previous_posts'], 10 );
		remove_filter( 'get_next_post_where', [$this, 'get_next_posts'], 10 );

		if ( isset( $this->site_options['cache_purge_pages'] ) ){
			remove_filter( 'pre_get_rocket_option_cache_purge_pages', [ $this, 'set_cache_purge_pages' ] );
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnUrls( $config, $expected ) {
		$this->markTestSkipped('This test returns inconsistent results. Need to revisit.');
		global $post;

		$post_id = $this->create_current_post( $config['post_data'] );

		$post    = get_post($post_id);
		$options = isset( $config['options'] ) ? $config['options'] : [];

		if ( isset( $options['page_for_posts'] ) ) {
			$options['page_for_posts'] = $this->page_for_posts;
		}

		$this->site_options = array_merge($this->site_options, $options);

		foreach ( $this->site_options as $option_name => $option_value ) {
			add_filter( 'pre_option_'.$option_name, [$this, 'prepare_option'], 10, 3 );
		}

		if ( isset( $this->site_options['cache_purge_pages'] ) ){
			add_filter( 'pre_get_rocket_option_cache_purge_pages', [ $this, 'set_cache_purge_pages' ] );
		}

		add_filter( 'get_previous_post_where', [$this, 'get_previous_posts'], 10, 2 );
		add_filter( 'get_next_post_where', [$this, 'get_next_posts'], 10, 2 );

		// Sort actual and expected arrays to compare between them.
		$actual = rocket_get_purge_urls( $post_id, $post );
		asort($expected);
		asort($actual);
		$this->assertEquals( array_values( $expected ) , array_values( $actual ) );
	}

	public function prepare_option( $pre_option, $option, $default ) {
		return isset( $this->site_options[$option] ) ? $this->site_options[$option] : $default;
	}

	public function get_previous_posts( $where, $in_same_term ) {
		if ( $in_same_term && isset( $this->post_data['prev_in_term_post_id'] ) ) {
			$where = " WHERE p.ID = {$this->posts_map[ $this->post_data['prev_in_term_post_id'] ]} ";
		}

		if ( ! $in_same_term && isset( $this->post_data['prev_post_id'] ) ) {
			$where = " WHERE p.ID = {$this->posts_map[ $this->post_data['prev_post_id'] ]} ";
		}

		return $where;
	}

	public function get_next_posts( $where, $in_same_term ) {
		if ( $in_same_term && isset( $this->post_data['next_in_term_post_id'] ) ) {
			$where = " WHERE p.ID = {$this->posts_map[ $this->post_data['next_in_term_post_id'] ]} ";
		}

		if ( ! $in_same_term && isset( $this->post_data['next_post_id'] ) ) {
			$where = " WHERE p.ID = {$this->posts_map[ $this->post_data['next_post_id'] ]} ";
		}

		return $where;
	}

	private function create_posts( $posts ) {
		foreach ( $posts as $post_id => $url ) {
			$post_name = str_replace("http://example.org/", "", $url);
			$post_details = [
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
		}
	}

	private function create_authors( $authors ) {
		foreach ( $authors as $author_id => $url ) {
			$this->authors_map[$author_id] = $this->factory->user->create( [ 'user_login' => str_replace("http://example.org/author/", "", $url), 'role' => 'editor' ] );
		}
	}

	private function create_current_post( $current_post_data ) {
		// Create the custom post firstly.
		if ( ! post_type_exists( $current_post_data['post_type'] ) ){
			register_post_type( $current_post_data['post_type'], ['has_archive' => true] );
		}

		$post_data = [
			'post_title'  => $current_post_data['post_name'],
			'post_name'   => $current_post_data['post_name'],
			'post_type'   => $current_post_data['post_type'],
			'post_author' => $this->authors_map[$current_post_data['post_author']],
		];

		if ( isset( $current_post_data['ancestors'] ) ){
			$post_data['post_parent'] = $this->posts_map[4];
		}

		$this->post_data = $current_post_data;

		return $this->factory->post->create( $post_data );
	}

	public function set_cache_purge_pages() {
		return $this->site_options['cache_purge_pages'];
	}

}
