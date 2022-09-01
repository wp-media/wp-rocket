<?php

namespace WP_Rocket\Tests\Unit\inc\admin\ui\notices;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\Notices;
use wpdb;

/**
 * @covers \WP_Rocket\Engine\Admin\Notices::maybe_display_as_missed_tables_notice
 *
 * @group admin
 * @group notices
 */
class Test_MaybeDisplayAsMissedTables extends TestCase {

    private $wpdb, $notices;

    public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/wpdb.php';
	}

    protected function setUp(): void {
		parent::setUp();
        Functions\stubTranslationFunctions();

        $this->notices = new Notices();

		$GLOBALS['wpdb'] = $this->wpdb = new wpdb();
	}

    protected function tearDown(): void {
		unset( $GLOBALS['wpdb'] );

		parent::tearDown();
	}
	
    /**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
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
            Functions\expect( 'menu_page_url' )
            ->with( 'action-scheduler', false )
            ->andReturn( $config['as_tool_link'] );

            Functions\expect( 'rocket_notice_html' )
				->with( $expected );
        }
        else{
            Functions\expect( 'rocket_notice_html' )->never();
        }

        $this->notices->maybe_display_as_missed_tables_notice();
    }
}
