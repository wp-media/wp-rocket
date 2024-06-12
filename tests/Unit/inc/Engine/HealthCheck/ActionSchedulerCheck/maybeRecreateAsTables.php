<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\HealthCheck\ActionSchedulerCheck;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\HealthCheck\ActionSchedulerCheck;
use WP_Rocket\Tests\Unit\TestCase;
use wpdb;

/**
 * Test class covering \WP_Rocket\Engine\HealthCheck\ActionSchedulerCheck::maybe_recreate_as_tables
 * @group  HealthCheck
 */
class Test_MaybeRecreateAsTables extends TestCase {
	private $as_check;
	private $wpdb;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/wpdb.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/HealthCheck/ActionSchedulerCheck/ActionScheduler_StoreSchema.php';
        require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/HealthCheck/ActionSchedulerCheck/ActionScheduler_LoggerSchema.php';
	}

	protected function setUp(): void {
		parent::setUp();

		$this->as_check = new ActionSchedulerCheck();

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
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\expect( 'set_transient' )->once();

		$this->wpdb->setTableRows( $config['found_as_tables'] );

		$this->assertSame( $expected, $this->as_check->maybe_recreate_as_tables() );
	}
}
