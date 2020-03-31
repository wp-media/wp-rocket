<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::get_rocket_post_terms_urls
 * @group  Functions
 * @group  Posts
 */
class Test_GetRocketPostTermsUrls extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedUrls( $post_data, $terms, $expected ) {
		$post_id    = 10;
		$taxonomies = $this->getTaxonomies( $terms );

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

			foreach ( $terms_objs as $term ) {
				Functions\expect( 'get_term_link' )
					->once()
					->with( $term->slug, $taxonomy->name )
					->andReturn( $expected[ $index ] );
				$index++;
				Functions\when( 'is_wp_error' )->justReturn( false );
			}
		}
		Filters\expectApplied( 'rocket_post_terms_urls', $expected )->once();

		$this->assertSame( $expected, get_rocket_post_terms_urls( $post_id ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}

	private function getTaxonomies( array $config ) {
		$taxonomies = [];

		$taxonomies['category'] = (object) [ 'name' => 'category', 'public' => true ];

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
			$terms[] = (object) [ 'slug' => isset( $term['name'] ) ? $term['name'] : $term['slug'] ];
		}

		return $terms;
	}
}
