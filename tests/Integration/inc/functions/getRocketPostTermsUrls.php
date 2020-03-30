<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::get_rocket_post_terms_urls
 * @group  Functions
 * @group  Posts
 */
class Test_GetRocketPostTermsUrls extends TestCase {
	private static $user_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create(
			[
				'role'          => 'editor',
				'user_nicename' => 'rocket_tester',
			]
		);
	}

	public function setUp() {
		parent::setUp();

		wp_set_current_user( self::$user_id );
		$this->set_permalink_structure( "/%postname%/" );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedUrls( $post_data, $terms, $expected ) {
		$post_id = $this->factory->post->create( $post_data );

		$this->setTags( $post_id, $terms['post_tag'] );
		$this->setCategories( $post_id, $terms['category'], $expected );
		$this->setCustomTerms( $post_id, $terms['custom'] );

		$this->assertSame( $expected, get_rocket_post_terms_urls( $post_id ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}

	private function setTags( $post_id, $config ) {
		if ( empty( $config ) ) {
			return;
		}

		$tags = [];

		foreach ( $config as $tag ) {
			$tags[] = $this->factory->tag->create( $tag );
		}

		wp_set_object_terms( $post_id, $tags, 'post_tag' );
	}

	private function setCategories( $post_id, array $config, array &$expected ) {
		if ( empty( $config ) ) {
			return;
		}

		$categories = [];

		foreach ( $config as $cat ) {
			$cat_id = $this->factory->category->create( $cat );

			foreach ( $expected as $index => $url ) {
				$expected[ $index ] = str_replace( $cat['slug'], $cat_id, $url );
			}

			$categories[] = $cat_id;
		}

		wp_set_object_terms( $post_id, $categories, 'category' );
	}

	private function setCustomTerms( $post_id, array $config ) {
		if ( empty( $config ) ) {
			return;
		}

		register_taxonomy( $config['tax']['tax'], $config['tax']['object_type'], $config['tax']['args'] );

		$terms = [];

		foreach ( $config['terms'] as $term ) {
			$terms[] = $this->factory->term->create( $term );
		}

		if ( empty( $terms ) ) {
			return;
		}
		wp_set_object_terms( $post_id, $terms, $config['tax']['tax'] );
	}
}
