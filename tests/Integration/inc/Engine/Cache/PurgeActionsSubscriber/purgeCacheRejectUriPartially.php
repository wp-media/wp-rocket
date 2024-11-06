<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeActionsSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\PurgeActionsSubscriber:purge_cache_reject_uri_partially
 *
 * @group PurgeActions
 */
class Test_PurgeCacheRejectUriPartially extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/Purge/purgeCacheRejectUriPartially.php';

	public function set_up() {
		parent::set_up();

		// Install the preload cache table to prevent DB error caused by permalink changed.
		self::installPreloadCacheTable();

		$this->set_permalink_structure( '/%postname%/' );
	}

	public function tear_down() {
		// Uninstall the preload cache table.
		self::uninstallPreloadCacheTable();

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldPurgePartiallyWhenCacheRejectUriOptionIsChanged( $config, $expected ) {
		if ( ! isset( $expected['cleaned'] ) ) {
			Functions\expect( 'home_url' )->never();
			Functions\expect( 'rocket_clean_files' )->never();
		}
		else {
			$this->factory->post->create(
				[
					'post_name'  => 'hello-world',
					'post_title' => 'hello world',
				]
			);

			$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

			do_action( 'update_option_' . WP_ROCKET_SLUG, $config['old_value'], $config['value'] );

			$this->checkEntriesDeleted( $expected['cleaned'] );
			$this->checkShouldNotDeleteEntries();
		}
	}
}
