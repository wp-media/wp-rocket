<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeActionsSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\PurgeActionsSubscriber:purge_post_terms_urls
 *
 * @uses ::get_rocket_parse_url
 *
 * @group PurgeActions
 * @group vfs
 */
class Test_PurgePostTermsUrls extends FilesystemTestCase {
	private static $user_id      = 0;
	protected $path_to_test_data = '/inc/Engine/Cache/Purge/purgePostTermsUrls.php';

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create(
			[
				'role'          => 'editor',
				'user_nicename' => 'rocket_tester',
			]
		);
	}

	public function set_up() {
		parent::set_up();

		// Install the preload cache table to prevent DB error caused by permalink changed.
		self::installPreloadCacheTable();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		wp_set_current_user( self::$user_id );
		$this->set_permalink_structure( "/%postname%/" );
	}

	public function tear_down() {
		// Uninstall the preload cache table.
		self::uninstallPreloadCacheTable();

		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedUrls( $post_data, $terms, $expected ) {
		$post = $this->factory->post->create_and_get( $post_data );

		$this->setTags( $post->ID, $terms['post_tag'] );
		$this->setCategories( $post->ID, $terms['category'] );
		$this->setCustomTerms( $post->ID, $terms['custom'] );

		$this->generateEntriesShouldExistAfter( $expected );

		do_action( 'after_rocket_clean_post', $post, [], '' );

		$this->checkEntriesDeleted( $expected );
		$this->checkShouldNotDeleteEntries();
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

	private function setCategories( $post_id, array $config ) {
		if ( empty( $config ) ) {
			return;
		}

		$categories = [];

		foreach ( $config as $cat ) {
			if ( isset( $cat['parent'] ) ) {
				$parent_slug   = $cat['parent'];
				$parent_cat_id = $this->factory->category->create( [ 'slug' => $parent_slug ] );
				$cat['parent'] = $parent_cat_id;
			}
			$cat_id       = $this->factory->category->create( $cat );
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
