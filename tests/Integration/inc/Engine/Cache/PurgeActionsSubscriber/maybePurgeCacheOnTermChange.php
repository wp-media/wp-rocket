<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeActionsSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\GlobTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\PurgeActionsSubscriber:maybe_purge_cache_on_term_change
 * @uses ::rocket_clean_domain
 * @group purge_actions
 * @group vfs
 */
class Test_MaybePurgeCacheOnTermChange extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/PurgeActionsSubscriber/maybePurgeCacheOnTermChange.php';
	protected static $not_public_term;
	protected static $public_term;
	protected $deleted_files = [
		'wp-content/cache/wp-rocket/example.org/index.html',
		'wp-content/cache/wp-rocket/example.org/index.html_gzip',
		'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html',
		'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html_gzip',
	];

	public static function wpSetUpBeforeClass( $factory ) {
		register_taxonomy(
			'not_public',
			'post',
			[
				'public'             => false,
				'publicly_queryable' => false,
			]
		);

		self::$not_public_term = $factory->term->create_and_get(
			[
				'name'     => 'test_term',
				'taxonomy' => 'not_public',
			]
		);

		self::$public_term = $factory->term->create_and_get(
			[
				'name'     => 'news',
				'taxonomy' => 'category',
			]
		);
	}

	public function testShouldNotPurgeCacheWhenTaxonomyNotPublic() {
		do_action( 'create_term', self::$not_public_term->term_id, self::$not_public_term->term_taxonomy_id, self::$not_public_term->taxonomy );
		do_action( 'edit_term', self::$not_public_term->term_id, self::$not_public_term->term_taxonomy_id, self::$not_public_term->taxonomy );
		do_action( 'pre_delete_term', self::$not_public_term->term_id, self::$not_public_term->term_taxonomy_id, self::$not_public_term->taxonomy );

		// Check no files were deleted.
		foreach( $this->original_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}

	public function testShouldPurgeCacheWhenTaxonomyPublicWhenEditing() {
		do_action( 'edit_term', self::$public_term->term_id, self::$public_term->term_taxonomy_id, self::$public_term->taxonomy );

		// Check no files were deleted.
		foreach( $this->deleted_files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file ) );
		}

		// Check no files were deleted.
		foreach( array_diff( $this->deleted_files, $this->original_files ) as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}

	public function testShouldPurgeCacheWhenTaxonomyPublicWhenCreating() {
		do_action( 'create_term', self::$public_term->term_id, self::$public_term->term_taxonomy_id, self::$public_term->taxonomy );

		// Check no files were deleted.
		foreach( $this->deleted_files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file ) );
		}

		// Check no files were deleted.
		foreach( array_diff( $this->deleted_files, $this->original_files ) as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}

	public function testShouldPurgeCacheWhenTaxonomyPublicWhenDeleting() {
		do_action( 'pre_delete_term', self::$public_term->term_id, self::$public_term->term_taxonomy_id, self::$public_term->taxonomy );

		// Check no files were deleted.
		foreach( $this->deleted_files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file ) );
		}

		// Check no files were deleted.
		foreach( array_diff( $this->deleted_files, $this->original_files ) as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}
}
