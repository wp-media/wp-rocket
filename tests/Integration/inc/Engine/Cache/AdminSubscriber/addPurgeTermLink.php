<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering WP_Rocket\Engine\Cache\AdminSubscriber::add_purge_term_link
 *
 * @group AdminOnly
 * @group Cache
 */
class Test_AddPurgeTermLink extends AdminTestCase {
	private $tag;

	public function tear_down() {
		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );
		wp_delete_term( $this->tag->term_id, 'post_tag' );
		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddCallbackForEachTerm( $config, $expected ) {
		$this->tag = $this->factory->tag->create_and_get( [ 'name' => 'Ipseum' ] );

		if ( $config['cap'] ) {
			$this->setRoleCap( 'administrator', 'rocket_purge_terms' );
			$this->setCurrentUser( 'administrator' );
			Functions\expect( 'wp_create_nonce' )
				->once()
				->with( "purge_cache_term-{$this->tag->term_id}" )
				->andReturn( $config['nonce'] );
		}
		$this->setEditTagsAsCurrentScreen( 'post_tag' );
		// Prevent trying to create tables on admin_init (ATF, cache, RUCSS).
		self::removeDBHooks();
		$this->fireAdminInit();

		$this->hasCallbackRegistered( 'post_tag_row_actions', AdminSubscriber::class, 'add_purge_term_link' );

		$actions = apply_filters( 'post_tag_row_actions', [], $this->tag );

		if ( $config['cap'] ) {
			$this->assertArrayHasKey( 'rocket_purge', $actions );

			// Populate the term's ID.
			$expected = str_replace( 'term-1', "term-{$this->tag->term_id}", $expected );
			$this->assertSame( $expected, $actions['rocket_purge'] );
		} else {
			$this->assertArrayNotHasKey( 'rocket_purge', $actions );
		}
	}
}
