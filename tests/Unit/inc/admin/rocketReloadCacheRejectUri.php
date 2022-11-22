<?php

namespace WP_Rocket\Tests\Unit\inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use wpdb;

/**
 * @covers ::rocket_reload_cache_reject_uri
 *
 * @group admin
 * @group Options
 */
class Test_RocketReloadCacheRejectUri extends FilesystemTestCase {
    protected $path_to_test_data = '/inc/admin/rocketReloadCacheRejectUri.php';
    private $wpdb;

    protected function setUp(): void {
		parent::setUp();

        require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/options.php';
        require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/wpdb.php';

		$GLOBALS['wpdb'] = $this->wpdb = new wpdb();
	}

    protected function tearDown(): void {
		unset( $GLOBALS['wpdb'] );

		parent::tearDown();
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
            if ( isset( $config['db_url_result'] ) ) {
                $this->wpdb->setTableRows( $config['db_url_result'] );
            }

            foreach ( $config['value']['cache_reject_uri'] as $path ) {
                if ( '/hello-world/' === $path ) {
                    Functions\expect( 'home_url' )
                    ->once()
                    ->with( $path )
                    ->andReturn( 'https://example.org' . $path );
                }
            }

            Functions\expect( 'rocket_clean_files' )
            ->once()
            ->with( $config['urls'] )
            ->andReturnNull();
        }

        rocket_reload_cache_reject_uri( $config['old_value'], $config['value'] );
    }
}