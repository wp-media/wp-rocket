<?php

namespace WP_Rocket\Tests\Integration\inc\common;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\GlobTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_post_cache_on_slug_change
 * @uses   ::rocket_clean_files
 * @group  Purge
 * @group  vfs
 */
class TestRocketCleanPostCacheOnSlugChange extends FilesystemTestCase {
	use GlobTrait;

	protected $path_to_test_data = '/inc/common/rocketCleanPostCacheOnSlugChange.php';
	private static $user_id = 0;
	private $posts = [];

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'editor' ] );
	}

	public function setUp() {
		parent::setUp();

		wp_set_current_user( self::$user_id );
		$this->set_permalink_structure( "/%postname%/" );
		set_current_screen( 'edit.php' );

		foreach ( $this->config['posts'] as $slug => $post_data ) {
			$this->posts[ $slug ] = $this->factory->post->create_and_get( $post_data );
		}
	}

	public function tearDown() {
		parent::tearDown();

		remove_action( 'after_rocket_clean_file', [ $this, 'after_rocket_clean_file_cb' ] );
	}

	public function testShouldRegisterCallbackToPrePostUpdate() {
		$this->assertTrue( function_exists( 'rocket_clean_post_cache_on_slug_change' ) );
		$this->assertEquals( PHP_INT_MAX, has_action( 'pre_post_update', 'rocket_clean_post_cache_on_slug_change' ) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testTestData( $slug, $new_post_data ) {
		$post                     = $this->posts[ $slug ];
		$new_post_data['post_ID'] = $post->ID;
		$post_cache_dir           = trailingslashit( $this->filesystem->getUrl( $this->config['vfs_dir'] . $post->post_name ) );

		// Check the post is cached before editing it.
		$this->assertTrue( $this->filesystem->exists( $post_cache_dir ) );
		$this->assertNotEmpty( $this->filesystem->getListing( $post_cache_dir ) );

		$rocket_clean_files_should_run = isset( $new_post_data['post_name'] ) && $new_post_data['post_name'] !== $post->post_name;
		if ( $rocket_clean_files_should_run ) {
			add_action( 'after_rocket_clean_file', [ $this, 'after_rocket_clean_file_cb' ] );
		}

		// Run it.
		$post_id      = edit_post( $new_post_data );
		$updated_post = get_post( $post_id );

		if ( $rocket_clean_files_should_run ) {

			// Check the slug (post name) changed.
			$this->assertNotSame( $post->post_name, $updated_post->post_name );

			// Check rocket_clean_files() ran.
			$this->assertFalse( $this->filesystem->exists( $post_cache_dir ) );
		} else {

			// Check the slug (post name) did not changed.
			$this->assertSame( $post->post_name, $updated_post->post_name );

			// Check the post's cache files and directory were not purged.
			$this->assertTrue( $this->filesystem->exists( $post_cache_dir ) );
			$this->assertNotEmpty( $this->filesystem->getListing( $post_cache_dir ) );
		}
	}

	/**
	 * Callback is required, as the virtual filesystem does not work with glob().
	 */
	public function after_rocket_clean_file_cb( $url ) {
		$url = str_replace( 'http://example.org*', 'http://example.org', $url );
		$dir = trailingslashit( WP_ROCKET_CACHE_PATH . rocket_remove_url_protocol( $url ) );
		$this->deleteFiles( $dir, $this->filesystem );
		$this->filesystem->rmdir( $dir );
	}

	public function testShouldBailOutWhenOldSlugIsEmpty() {
		foreach ( $this->posts as $slug => $post ) {
			// Shouldn't run after we edit the post.
			Functions\expect( 'rocket_clean_files' )->with( get_the_permalink( $post->ID ) )->never();

			// Modify the WP Cache to empty the slug ('post_name') in memory.
			$post->post_name = '';
			wp_cache_replace( $post->ID, $post, 'posts' );
			$this->assertEmpty( get_post( $post->ID )->post_name );

			// Edit the post.
			$post_id = edit_post(
				[
					'post_content' => "[Updated] {$post->post_content}",
					'post_ID'      => $post->ID,
				]
			);

			// The original is empty, but the updated one is not.
			$this->assertNotEmpty( get_post( $post_id )->post_name );
		}
	}
}
