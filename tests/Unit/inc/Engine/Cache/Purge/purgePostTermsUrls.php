<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\Purge;

use WP_Post;
use WP_Term;
use Mockery;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\Cache\Purge;

/**
 * @covers ::get_rocket_post_terms_urls
 * @group  Purge
 * @group  purge_actions
 */
class Test_PurgePostTermsUrls extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/Purge/purgePostTermsUrls.php';

	public function setUp() {
		parent::setUp();

		$this->purge = new Purge( $this->filesystem );
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['wp_rewrite'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedUrls( $post_data, $terms, $expected ) {
		$post_id    = 10;
		$taxonomies = $this->getTaxonomies( $terms );

		$post_mocked     = Mockery::mock( WP_Post::class );
		$post_mocked->ID = $post_id;

		$term_mocked = Mockery::mock( WP_Term::class );

		Functions\expect( 'get_post_type' )
			->once()
			->with( $post_id )
			->andReturn( 'post' );
		Functions\expect( 'get_object_taxonomies' )
			->once()
			->with( 'post', 'objects' )
			->andReturn( $taxonomies );

		$index = 0;
		foreach ( $taxonomies as $type => $taxonomy ) {
			if ( ! $taxonomy->public || 'product_shipping_class' === $taxonomy->name ) {
				continue;
			}

			$terms_objs = $this->getTerms(
				$type,
				'custom' === $type ? $terms['custom']['terms'] : $terms[ $type ]
			);

			Functions\expect( 'get_the_terms' )
				->once()
				->with( $post_id, $taxonomy->name )
				->andReturn( $terms_objs );

			Functions\expect( 'is_wp_error' )
				->once()
				->with( $terms_objs )
				->andReturn( false );

			foreach ( $terms_objs as $term ) {
				Functions\expect( 'get_term_link' )
					->once()
					->with( $term->slug, $taxonomy->name )
					->andReturn( 'https://example.org/' . $term->slug );
				$index++;
				Functions\expect( 'is_wp_error' )
					->once()
					->with( 'https://example.org/' . $term->slug )
					->andReturn( false );

				if ( isset( $taxonomy->is_taxonomy_hierarchical ) && isset( $term->parent ) ) {
					Functions\expect( 'is_taxonomy_hierarchical' )
						->once()
						->with( $taxonomy->name )
						->andReturn( $taxonomy->is_taxonomy_hierarchical );
					Functions\expect( 'get_ancestors' )
						->once()
						->with( $term->term_id, $taxonomy->name )
						->andReturn( [ $term->parent ] );

					$term_mocked->slug = $term->parent;

					Functions\expect( 'get_term' )
						->once()
						->with( $term->parent, $taxonomy->name )
						->andReturn( $term_mocked );
					Functions\expect( 'get_term_link' )
						->once()
						->with( $term->parent , $taxonomy->name )
						->andReturn( 'https://example.org/' . $term->parent );
					$index++;
				} else {
					Functions\expect( 'is_taxonomy_hierarchical' )
						->once()
						->with( $taxonomy->name )
						->andReturn( false );
				}
			}
		}
		Filters\expectApplied( 'rocket_post_terms_urls', $expected )->once();

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );

		$this->purge->purge_post_terms_urls( $post_mocked );

		$this->checkEntriesDeleted( $expected );
	}

	private function getTaxonomies( array $config ) {
		$taxonomies = [];

		$taxonomies['category'] = (object) [ 'name' => 'category', 'public' => true,  'is_taxonomy_hierarchical' => true ];

		if ( ! empty( $config['post_tag'] ) ) {
			$taxonomies['post_tag'] = (object) [ 'name' => 'post_tag', 'public' => true ];
		}

		if ( ! empty( $config['custom'] ) ) {
			$taxonomies['custom'] = (object) [
				'name' => $config['custom']['tax']['tax'],
				'public' => isset( $config['custom']['tax']['args']['public'] ) ? $config['custom']['tax']['args']['public'] : true
			];
		}

		return $taxonomies;
	}

	private function getTerms( $type, $config ) {
		$terms = [];

		// Uncategorized term.
		if ( 'category' === $type && empty( $config ) ) {
			$terms[] = (object) [ 'slug' => 'uncategorized' ];
		}

		foreach ( $config as $term ) {
			$terms[] = (object) [ 'term_id' => rand(1, 100), 'slug' => isset( $term['name'] ) ? $term['name'] : $term['slug'], 'parent' => isset( $term['parent']) ? $term['parent'] : null ];
		}

		return $terms;
	}
}
