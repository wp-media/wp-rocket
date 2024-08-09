<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeActionsSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\PurgeActionsSubscriber:purge_dates_archives
 *
 * @uses ::get_rocket_parse_url
 *
 * @group PurgeActions
 * @group vfs
 */
class Test_PurgeDatesArchives extends FilesystemTestCase {
	private static $user_id = 0;
	protected $path_to_test_data = '/inc/Engine/Cache/Purge/purgeDatesArchives.php';

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

		wp_set_current_user( self::$user_id );
		$this->set_permalink_structure( "/%postname%/" );
		set_current_screen( 'edit.php' );
	}

	public function tear_down() {
		// Uninstall the preload cache table.
		self::uninstallPreloadCacheTable();

		set_current_screen( 'front' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanCache( $post_data, $cleaned ) {
		$post = $this->factory->post->create_and_get( $post_data );

		$this->generateEntriesShouldExistAfter( $cleaned );

		do_action( 'after_rocket_clean_post', $post, [], '' );

		$this->checkEntriesDeleted( $cleaned );
		$this->checkShouldNotDeleteEntries();
   }
}
