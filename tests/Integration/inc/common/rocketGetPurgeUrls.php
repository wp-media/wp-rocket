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
		'stylesheet' => '',
	];

	public function setUp() {
		parent::setUp();

		Functions\expect('get_permalink')
			->withAnyArgs()
			->andReturnUsing( function ( $post_id ) {
				return $this->get_post_url( $post_id );
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

		$post_id = isset( $config['post_data']['ID'] ) ? $config['post_data']['ID'] : 0;
		$post    = isset( $config['post_data'] ) ? (object) $config['post_data'] : null;
		$options = isset( $config['options'] ) ? $config['options'] : [];

		$this->site_options = array_merge($this->site_options, $options);

		foreach ( $this->site_options as $option_name => $option_value ) {
			add_filter( 'pre_option_'.$option_name, [$this, 'prepare_option'], 10, 3 );
		}

		Functions\expect('get_rocket_sample_permalink')
			->once()
			->with( $post_id )
			->andReturn( ['http://www.example.org/%postname%', $post->post_name] );

		Functions\expect('get_adjacent_post')
			->andReturnUsing( function ( $in_same_term, $excluded_terms, $previous ) use ($post) {
				if ( ! $previous ) {
					//next
					if( ! $in_same_term ) {
						return isset( $post->next_post_id ) ? $post->next_post_id : false;
					}
					return isset( $post->next_in_term_post_id ) ? $post->next_in_term_post_id : false;
				}

				//prev
				if (! $in_same_term ) {
					return isset( $post->prev_post_id ) ? $post->prev_post_id : false;
				}

				return isset( $post->prev_in_term_post_id ) ? $post->prev_in_term_post_id : false;
			} );

		Functions\expect('get_author_posts_url')
			->once()
			->with($post->post_author)
			->andReturn( $this->get_author_url( $post->post_author ) );

		Functions\expect('get_post_ancestors')
			->once()
			->with($post_id)
			->andReturn( isset( $post->ancestors ) ? $post->ancestors : [] );

		if( 'post' !== $post->post_type ){
			$archive_url = $this->get_archive_url( $post->post_type );
			Functions\expect('get_post_type_archive_link')
				->once()
				->with( $post->post_type )
				->andReturn( $archive_url );
		}


		$actual = rocket_get_purge_urls( $post_id, $post );
		$this->assertEquals($expected, $actual);
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
