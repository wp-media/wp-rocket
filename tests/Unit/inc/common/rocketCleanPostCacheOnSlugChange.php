<?php

namespace WP_Rocket\Tests\Unit\inc\common;

use Brain\Monkey\Functions;

/**
 * Test class covering ::rocket_clean_post_cache_on_slug_change
 * @uses   ::rocket_clean_files
 *
 * @group  Common
 * @group  Purge
 */
class Test_RocketCleanPostCacheOnSlugChange extends TestCase {

	public function testShouldBailOutWhenPostStatusIsNotCorrect() {
		$post_id   = 10;
		$post_name = 'new-post-name-slug';

		Functions\expect( 'get_the_permalink' )->never();
		Functions\expect( 'rocket_clean_files' )->never();
		Functions\expect( 'get_post_field' )
			->with( 'post_name', $post_id )
			->never();

		foreach ( [ 'draft', 'pending', 'auto-draft' ] as $post_status ) {
			Functions\expect( 'get_post_field' )
				->once()
				->with( 'post_status', $post_id )
				->andReturn( $post_status );
			$this->assertNull( rocket_clean_post_cache_on_slug_change( $post_id, [ 'post_name' => $post_name ] ) );
		}
	}

	public function testShouldBailOutWhenSlugHasntChanged() {
		$post_id   = 50;
		$post_name = 'original-post-name-slug';

		Functions\expect( 'get_the_permalink' )->never();
		Functions\expect( 'rocket_clean_files' )->never();
		Functions\expect( 'get_post_field' )
			->ordered()
			->once()
			->with( 'post_status', $post_id )
			->andReturn( 'publish' )
			->andAlsoExpectIt()
			->once()
			->with( 'post_name', $post_id )
			->andReturn( $post_name ); // slug hasn't changed.

		$this->assertNull( rocket_clean_post_cache_on_slug_change( $post_id, [ 'post_name' => $post_name ] ) );
	}

	public function testShouldBailOutWhenOldSlugIsEmpty() {
		$post_id   = 100;
		$post_name = 'new-post-name-slug';

		Functions\expect( 'get_the_permalink' )->never();
		Functions\expect( 'rocket_clean_files' )->never();
		Functions\expect( 'get_post_field' )
			->ordered()
			->once()
			->with( 'post_status', $post_id )
			->andReturn( 'publish' )
			->andAlsoExpectIt()
			->once()
			->with( 'post_name', $post_id )
			->andReturn( '' ); // No slug saved in the database.

		$this->assertNull( rocket_clean_post_cache_on_slug_change( $post_id, [ 'post_name' => $post_name ] ) );
	}

	public function testShouldFireRocketCleanFilesWhenExistingPostStatusIsCorrect() {
		$post_id   = 200;
		$post_name = 'new-post-name-slug';
		$permalink = "https://wp-rocket.test/{$post_name}/";

		Functions\expect( 'get_post_field' )
			->ordered()
			->once()
			->with( 'post_status', $post_id )
			->andReturn( 'publish' )
			->andAlsoExpectIt()
			->once()
			->with( 'post_name', $post_id )
			->andReturn( 'original-post-name-slug' );

		Functions\expect( 'get_the_permalink' )
			->once()
			->with( $post_id )
			->andReturn( $permalink );

		Functions\expect( 'rocket_clean_files' )
			->once()
			->with( $permalink )
			->andReturnNull();

		$this->assertNull( rocket_clean_post_cache_on_slug_change( $post_id, [ 'post_name' => $post_name ] ) );
	}
}
