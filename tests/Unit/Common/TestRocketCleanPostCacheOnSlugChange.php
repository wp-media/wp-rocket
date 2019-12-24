<?php

namespace WP_Rocket\Tests\Unit\Inc\Common;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @runTestsInSeparateProcesses
 */
class TestRocketCleanPostCacheOnSlugChange extends TestCase {
	protected function setUp() {
		parent::setUp();

		Functions\when( 'get_option' )->justReturn( '' );

		require WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';
	}

	/**
	 * Test rocket_clean_post_cache_on_slug_change() should not fire rocket_clean_files() when the post status is
	 * 'draft', 'pending', or 'auto-draft'.
	 */
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

	/**
	 * Test rocket_clean_post_cache_on_slug_change() should not fire rocket_clean_files() when the slug (post name)
	 * hasn't changed, i.e. meaning when the previous post name (saved in the database) is still the same as the new one
	 * (passed into this callback).
	 */
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

	/**
	 * Test rocket_clean_post_cache_on_slug_change() should not fire rocket_clean_files() when the old slug (post name),
	 * i.e. saved in the database, is empty.
	 */
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

	/**
	 * Test rocket_clean_post_cache_on_slug_change() should fire rocket_clean_files() when the existing post status is
	 * correct
	 * (i.e. not draft, pending, or auto-draft), the existing/old slug (post name) and new slug are the same, and the
	 * existing/old slug is not empty.
	 */
	public function testShouldFireRocketCleanFiles() {
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
