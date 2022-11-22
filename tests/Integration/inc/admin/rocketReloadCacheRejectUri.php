<?php

namespace WP_Rocket\Tests\Integration\inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_reload_cache_reject_uri
 *
 * @group admin
 * @group Options
 */
class Test_RocketReloadCacheRejectUri extends FilesystemTestCase {
    protected $path_to_test_data = '/inc/admin/rocketReloadCacheRejectUri.php';

    public function set_up() {
        require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/options.php';
        
        remove_action( 'update_option_wp_rocket_settings', 'rocket_reload_cache_reject_uri' );

		parent::set_up();

		$this->set_permalink_structure( '/%postname%/' );

        add_action( 'update_option_wp_rocket_settings', 'rocket_reload_cache_reject_uri', 10, 2 );
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

            update_option( 'wp_rocket_settings', $config['value'] );

            $this->checkEntriesDeleted( $expected['cleaned'] );
            $this->checkShouldNotDeleteEntries();
        }
    }
}