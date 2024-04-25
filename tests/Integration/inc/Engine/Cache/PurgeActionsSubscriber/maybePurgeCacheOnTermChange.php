<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeActionsSubscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\PurgeActionsSubscriber:maybe_purge_cache_on_term_change
 * @uses   ::rocket_clean_domain
 *
 * @group  purge_actions
 * @group  vfs
 */
class Test_MaybePurgeCacheOnTermChange extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/PurgeActionsSubscriber/maybePurgeCacheOnTermChange.php';

	use DBTrait;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::installFresh();
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		register_taxonomy(
			'not_public',
			'post',
			[
				'public'             => false,
				'publicly_queryable' => false,
			]
		);
	}

	public function tear_down() {
		parent::tear_down();

		unregister_taxonomy( 'not_public' );
		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
		unset( $GLOBALS['debug_fs'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldPurgeWhenExpected( $action, $is_public, $expected ) {
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		if ( isset( $expected['debug'] ) && $expected['debug'] ) {
			$GLOBALS['debug_fs'] = true;
		}

		if ( $is_public ) {
			$term_config = [
				'name'     => 'news',
				'taxonomy' => 'category',
			];

		} else {
			$term_config = [
				'name'     => 'test_term',
				'taxonomy' => 'not_public',
			];
		}

		$term = $this->factory->term->create_and_get( $term_config );
		do_action( $action, $term->term_id, $term->term_taxonomy_id, $term->taxonomy );

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}
