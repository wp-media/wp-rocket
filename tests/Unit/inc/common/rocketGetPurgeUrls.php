<?php
namespace WP_Rocket\Tests\Unit\inc\common;

use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering ::rocket_clean_cache_theme_update
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

	public function setUp() : void {
		parent::setUp();

		Functions\expect( 'get_option' )->withAnyArgs()->andReturnUsing( function( $option_name, $default=null ) {
			return isset( $this->site_options[$option_name] ) ? $this->site_options[$option_name] : $default;
		} );

		Functions\expect( 'get_rocket_option' )->withAnyArgs()->andReturnUsing( function( $option_name, $default=null ) {
			return isset( $this->site_options[$option_name] ) ? $this->site_options[$option_name] : $default;
		} );

		Functions\expect('get_permalink')
			->withAnyArgs()
			->andReturnUsing( function ( $post_id ) {
				return $this->get_post_url( $post_id );
			} );

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnUrls( $config, $expected ) {
		$post_id = isset( $config['post_data']['ID'] ) ? $config['post_data']['ID'] : 0;
		$post    = isset( $config['post_data'] ) ? (object) $config['post_data'] : null;
		$options = isset( $config['options'] ) ? $config['options'] : [];

		$this->site_options = array_merge($this->site_options, $options);

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );

		Functions\expect('get_rocket_sample_permalink')
			->once()
			->with( $post_id )
			->andReturn( ['http://example.org/%postname%/', ( ( ! in_array( $post->post_type, [ 'page', 'post' ] ) ) ? $post->post_type."/" : "" ) . $post->post_name] );

		Functions\expect('rocket_extract_url_component')
			->once()
			->with('http://example.org' . $post->url, PHP_URL_PATH)
			->andReturn( $post->url );

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

		Functions\expect('site_url')
			->once()
			->andReturn( $this->get_site_url() );

		Functions\expect('home_url')
			->zeroOrMoreTimes()
			->andReturn( $this->get_home_url() );

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

			if ( $archive_url ) {
				Functions\expect('is_ssl')
					->once()
					->andReturn( $config['is_ssl'] );

				$GLOBALS['wp_rewrite'] = (object) [
					'pagination_base' => 'page'
				];
			}
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

	private function get_site_url( ) {
		return isset( $this->config['urls']['site_url'] ) ? $this->config['urls']['site_url'] : '';
	}

	private function get_home_url( ) {
		return isset( $this->config['urls']['home_url'] ) ? $this->config['urls']['home_url'] : '';
	}

}
