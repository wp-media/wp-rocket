<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Database;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::drop_rucss_database_tables
 *
 * @group AdminOnly
 * @group RUCSS
 */
class Test_DropRucssDatabaseTables extends TestCase {
	use DBTrait;

	public static function setUpBeforeClass(): void {
		self::installFresh();

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::uninstallAll();
	}

	public function testShouldDoExpected( ){
		$container             = apply_filters( 'rocket_container', null );
		$database              = $container->get( 'rucss_database' );
		$rucss_resources_table = $container->get( 'rucss_resources_table' );
		$rucss_usedcss_table   = $container->get( 'rucss_usedcss_table' );

		$this->assertTrue( $rucss_resources_table->exists() );
		$this->assertTrue( $rucss_usedcss_table->exists() );

		$database->drop_rucss_database_tables();

		$this->assertFalse( $rucss_resources_table->exists() );
		$this->assertFalse( $rucss_usedcss_table->exists() );
	}
}
