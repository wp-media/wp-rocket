<?php

namespace WP_Rocket\Tests\Unit\inc\admin\ui\notices;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use wpdb;

/**
 * @covers ::rocket_notice_missed_table_as
 *
 * @group admin
 * @group notices
 */
class Test_RocketNoticeMissedTableAS extends TestCase {

    private $wpdb;

    public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/wpdb.php';
        require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/ui/notices.php';
	}

    protected function setUp(): void {
		parent::setUp();

		$GLOBALS['wpdb'] = $this->wpdb = new wpdb();
	}

    protected function tearDown(): void {
		unset( $GLOBALS['wpdb'] );

		parent::tearDown();
	}
	
    /**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
        Functions\when( 'get_current_screen' )->justReturn( $config['current_screen'] );

        if ( $config['current_screen'] != 'tools_page_action-scheduler' ) {
            Functions\expect( 'get_transient' )
            ->with( 'rocket_rucss_as_tables_count' )
            ->andReturn( $config['transient'] );

            Functions\when( 'is_admin' )->justReturn( $config['is_admin'] );
        }

        if ( isset( $config['found_as_tables'] ) ) {
            $this->wpdb->setTableRows( $config['found_as_tables'] );

            Functions\when( 'set_transient' )->justReturn( true );
        }

        if ( isset( $config['found_as_tables'] ) && count( $config['found_as_tables'] ) != 4 ) {
            Functions\expect( 'get_transient' )
            ->with( 'action-scheduler', false )
            ->andReturn( $config['as_tool_link'] );

            Functions\expect( 'rocket_notice_html' )
				->with( $expected );
        }
        else{
            Functions\expect( 'rocket_notice_html' )->never();
        }

        rocket_notice_missed_table_as();
    }
}
