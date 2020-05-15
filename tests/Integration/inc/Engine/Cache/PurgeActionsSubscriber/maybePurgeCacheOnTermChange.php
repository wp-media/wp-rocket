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
		do_action( 'delete_term', self::$not_public_term->term_id, self::$not_public_term->term_taxonomy_id, self::$not_public_term->taxonomy );

		// Check no files were deleted.
		foreach( $this->original_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldPurgeCacheWhenTaxonomyPublicWhenEditing( $action ) {
		$this->generateEntriesShouldExistAfter( $this->config['cleaned'] );

		do_action( $action, self::$public_term->term_id, self::$public_term->term_taxonomy_id, self::$public_term->taxonomy );

		$this->checkEntriesDeleted( $this->config['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}
