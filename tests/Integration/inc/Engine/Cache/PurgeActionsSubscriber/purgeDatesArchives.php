<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeActionsSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\PurgeActionsSubscriber:purge_dates_archives
 * @uses   ::get_rocket_parse_url
 *
 * @group  purge_actions
 * @group  vfs
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

	public function setUp() : void {
		parent::setUp();

		wp_set_current_user( self::$user_id );
		$this->set_permalink_structure( "/%postname%/" );
		set_current_screen( 'edit.php' );
	}

	public function tearDown() {
		parent::tearDown();

		set_current_screen( 'front' );
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
