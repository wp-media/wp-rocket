<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;
use ActionScheduler_StoreSchema;
use ActionScheduler_LoggerSchema;

/**
 * @covers \WP_Rocket\Engine\Admin\Notices::maybe_display_as_missed_tables_notice
 *
 * @group AdminOnly
 * @group notices
 */
class Test_MaybeDisplayAsMissedTables extends TestCase{

    private static $admin_user_id = 0;
	private static $contributer_user_id = 0;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$admin_role = get_role( 'administrator' );

		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		self::$contributer_user_id = static::factory()->user->create( [ 'role' => 'contributor' ] );
	}

    public function tear_down() {
        parent::tear_down();

        remove_filter( 'query', [ $this, 'drop_as_table' ] );
        $this->recreate_as_tables();
    }
	
    /**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {

        set_current_screen( $config['current_screen']->id );

        $this->configUser( $config['is_admin'] );

        add_filter( 'query', [ $this, 'drop_as_table' ] );

        ob_start();
		do_action( 'admin_notices' );
		$result = ob_get_clean();

        if ( isset( $expected['notice'] ) ) {
            $this->assertStringContainsString(
                $this->format_the_html( $expected['html'] ),
                $this->format_the_html( $result )
            );
        }
        else{
            $this->assertStringNotContainsString( $this->format_the_html( $expected['html'] ), $this->format_the_html( $result ) );
        }
    }

    private function configUser( $is_admin ) {
        $user_id = $is_admin ? self::$admin_user_id : self::$contributer_user_id;

        wp_set_current_user( $user_id );
    }

    public function drop_as_table() {
        global $wpdb;

        return "DROP TABLE IF EXISTS {$wpdb->prefix}actionscheduler_claims";
    }

    private function recreate_as_tables() {
        $store_schema  = new ActionScheduler_StoreSchema();
		$logger_schema = new ActionScheduler_LoggerSchema();
		$store_schema->register_tables( true );
		$logger_schema->register_tables( true );
    }
}