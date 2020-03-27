<?php

namespace WP_Rocket\Tests\Unit\inc\common;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_clean_post_cache_on_slug_change
 * @group Purge
 * @group vfs
 */
class Test_RocketCleanPostCacheOnSlugChange extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/common/rocketCleanPostCacheOnSlugChange.php';
	private $posts = [];

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_option' )->justReturn( '' );

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';

		$post_id = 5;
		foreach ( $this->config['posts'] as $slug => $post_data ) {
			$this->posts[ $slug ] = (object) array_merge(
				[
					'ID'          => $post_id,
					'post_name'   => $slug,
					'post_status' => '',
				],
				$post_data
			);
			$post_id++;
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testTestData( $slug, $new_post_data ) {
		$post          = $this->posts[ $slug ];
		$new_post_data = array_merge( (array) $post, $new_post_data );

		Functions\expect( 'get_post_field' )
			->once()
			->with( 'post_status', $post->ID )
			->andReturn( $new_post_data['post_status'] );

		// Test when the post status is 'draft', 'pending', or 'auto-draft'.
		if ( in_array( $new_post_data['post_status'], [ 'draft', 'pending', 'auto-draft' ], true ) ) {
			$this->shouldBailOutWhenPostStatusIsNotCorrect( $post->ID );

			// Test when the slug (post_name) changes.
		} elseif ( $new_post_data['post_name'] !== $post->post_name ) {
			$this->shouldPurgeWhenPostStatusCorrectAndSlugChanged( $post->ID, $post->post_name );

			// Test when the slug (post name) did not change.
		} else {
			$this->shouldBailOutWhenSlugHasntChanged( $post->ID, $new_post_data['post_name'] );
		}

		// Run it.
		rocket_clean_post_cache_on_slug_change( $post->ID, $new_post_data );
	}

	private function shouldBailOutWhenPostStatusIsNotCorrect( $post_id ) {
		Functions\expect( 'get_post_field' )->with( 'post_name', $post_id )->never();
		Functions\expect( 'get_the_permalink' )->with( $post_id )->never();
		Functions\expect( 'rocket_clean_files' )->never();
	}

	private function shouldBailOutWhenSlugHasntChanged( $post_id, $post_name ) {
		Functions\expect( 'get_post_field' )
			->once()
			->with( 'post_name', $post_id )
			->andReturn( $post_name ); // slug hasn't changed.

		Functions\expect( 'get_the_permalink' )->with( $post_id )->never();
		Functions\expect( 'rocket_clean_files' )->never();
	}

	public function shouldPurgeWhenPostStatusCorrectAndSlugChanged( $post_id, $post_name ) {
		$permalink = "http://example.org/{$post_name}/";

		Functions\expect( 'get_post_field' )
			->once()
			->with( 'post_name', $post_id )
			->andReturn( $post_name ); // slug changed.

		Functions\expect( 'get_the_permalink' )
			->once()
			->with( $post_id )
			->andReturn( $permalink );

		Functions\expect( 'rocket_clean_files' )
			->once()
			->with( $permalink )
			->andReturnNull();
	}

	public function testShouldBailOutWhenOldSlugIsEmpty() {
		$post_id   = 100;
		$post_name = 'new-post-name-slug';

		Functions\expect( 'get_post_field' )
			->ordered()
			->once()
			->with( 'post_status', $post_id )
			->andReturn( 'publish' )
			->andAlsoExpectIt()
			->once()
			->with( 'post_name', $post_id )
			->andReturn( '' ); // No slug saved in the database.

		Functions\expect( 'get_the_permalink' )->never();
		Functions\expect( 'rocket_clean_files' )->never();

		rocket_clean_post_cache_on_slug_change( $post_id, [ 'post_name' => $post_name ] );
	}
}
