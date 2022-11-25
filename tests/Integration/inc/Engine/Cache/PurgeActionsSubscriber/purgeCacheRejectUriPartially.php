<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeActionsSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\PurgeActionsSubscriber:purge_cache_reject_uri_partially
 */
class Test_PurgeCacheRejectUriPartially extends FilesystemTestCase {
    protected $path_to_test_data = '/inc/Engine/Cache/Purge/purgeCacheRejectUriPartially.php';

    public function set_up() {
        
		parent::set_up();

		$this->set_permalink_structure( '/%postname%/' );
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
            $this->factory->post->create( [
                'post_name'  => 'hello-world',
                'post_title' => 'hello world',
            ] );

            $this->generateEntriesShouldExistAfter( $expected['cleaned'] );

            do_action('update_option_' . WP_ROCKET_SLUG , $config['old_value'], $config['value']);

            $this->checkEntriesDeleted( $expected['cleaned'] );
            $this->checkShouldNotDeleteEntries();
        }
    }
}