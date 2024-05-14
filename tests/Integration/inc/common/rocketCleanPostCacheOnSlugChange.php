<?php

namespace WP_Rocket\Tests\Integration\inc\common;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * Test class covering ::rocket_clean_post_cache_on_slug_change
 * @uses  ::rocket_clean_files
 * @group Purge
 */
class TestRocketCleanPostCacheOnSlugChange extends TestCase {
	/**
	 * User's ID.
	 * @var int
	 */
	private static $user_id = 0;
	/**
	 * Instance of the original post, ie before editing.
	 * @var WP_Post
	 */
	private $original_post;

	/**
	 * Set up the User ID before tests start.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'editor' ] );
	}

	/**
	 * Prepares the test environment before each test.
	 */
	public function set_up() {
		parent::set_up();

		wp_set_current_user( self::$user_id );
		$this->original_post = self::factory()->post->create_and_get(
			[
				'post_title'  => 'Some cool post',
				'content'     => 'Lorem ipsum dolor sit amet',
				'post_status' => 'publish',
			]
		);
		$this->set_permalink_structure( "/%postname%/" );
		set_current_screen( 'edit.php' );
	}

	/**
	 * Tests rocket_clean_post_cache_on_slug_change() should be registered to the "pre_post_update" action event.
	 */
	public function testShouldRegisterCallbackToPrePostUpdate() {
		$this->assertTrue( function_exists( 'rocket_clean_post_cache_on_slug_change' ) );
		$this->assertEquals( PHP_INT_MAX, has_action( 'pre_post_update', 'rocket_clean_post_cache_on_slug_change' ) );
	}

	/**
	 * Test rocket_clean_post_cache_on_slug_change() should not fire rocket_clean_files() when the post status is
	 * 'draft', 'pending', or 'auto-draft'.
	 */
	public function testShouldBailOutWhenPostStatusIsNotCorrect() {
		// Shouldn't run after we edit the post.
		Functions\expect( 'rocket_clean_files' )->never();

		// Edit the post's status.
		$post_id = edit_post(
			[
				'post_status' => 'pending', // changed the post status from 'publish' to 'pending'.
				'post_ID'     => $this->original_post->ID,
			]
		);

		// Double-check the post status did change.
		$this->assertEquals( 'pending', get_post( $post_id )->post_status ); // makes sure the status changed.
	}

	/**
	 * Test rocket_clean_post_cache_on_slug_change() should not fire rocket_clean_files() when the slug (post name)
	 * hasn't changed, i.e. meaning when the previous post name (saved in the database) is still the same as the new one
	 * (passed into this callback).
	 */
	public function testShouldBailOutWhenSlugHasntChanged() {
		// Shouldn't run after we edit the post.
		Functions\expect( 'rocket_clean_files' )->never();

		// Edit the post's content only. Slug should remain the same.
		$post_id = edit_post(
			[
				'post_content' => 'Updated content happened here',
				'post_ID'      => $this->original_post->ID,
			]
		);

		// Slug didn't change.
		$this->assertSame( $this->original_post->post_name, get_post( $post_id )->post_name );
	}

	/**
	 * Test rocket_clean_post_cache_on_slug_change() should not fire rocket_clean_files() when the old slug (post name),
	 * i.e. saved in the database, is empty.
	 */
	public function testShouldBailOutWhenOldSlugIsEmpty() {
		// Modify the WP Cache to empty the slug ('post_name') in memory.
		$this->original_post->post_name = '';
		wp_cache_replace( $this->original_post->ID, $this->original_post, 'posts' );
		$this->assertEmpty( get_post( $this->original_post->ID )->post_name );

		// Shouldn't run after we edit the post.
		Functions\expect( 'rocket_clean_files' )->never();

		// Edit the post.
		$post_id = edit_post(
			[
				'post_content' => 'Updated content happened here',
				'post_ID'      => $this->original_post->ID,
			]
		);

		// The original is empty, but the updated one is not.
		$this->assertNotEmpty( get_post( $post_id )->post_name );
	}

	/**
	 * Test rocket_clean_post_cache_on_slug_change() should fire rocket_clean_files() when the existing post status is
	 * correct (i.e. not draft, pending, or auto-draft), the existing/old slug (post name) and new slug are the same,
	 * and the existing/old slug is not empty.
	 */
	public function testShouldFireRocketCleanFiles() {
		// rocket_clean_files() should run.
		Functions\expect( 'rocket_clean_files' )
			->once()
			->with( get_permalink( $this->original_post->ID ) )
			->andReturn(); // don't do anything.

		// Edit the post.
		$post_id = edit_post(
			[
				'post_title'  => 'Updated Cool Post',
				'post_name'   => 'updated-cool-post',
				'post_status' => $this->original_post->post_status,
				'post_ID'     => $this->original_post->ID,
			]
		);

		// Double check the post names (slugs) are no longer the same.
		$this->assertNotSame( $this->original_post->post_name, get_post( $post_id )->post_name );
	}
}
