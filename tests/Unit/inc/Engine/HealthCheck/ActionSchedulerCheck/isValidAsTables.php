<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\HealthCheck\ActionSchedulerCheck;

use WP_Rocket\Engine\HealthCheck\ActionSchedulerCheck;
use WP_Rocket\Tests\Unit\TestCase;
use wpdb;

/**
 * @covers \WP_Rocket\Engine\HealthCheck\ActionSchedulerCheck::is_valid_as_tables
 * @group  HealthCheck
 */
class Test_IsValidAsTables extends TestCase {
	private $as_check;
	private $wpdb;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/wpdb.php';
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
		$this->wpdb->setTableRows( $config['found_as_tables'] );

		$this->assertSame( $expected, $this->as_check->is_valid_as_tables() );
	}
}
