<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Database;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::drop_rucss_database_tables
 *
 * @group  RUCSS
 */
class Test_DropRucssDatabaseTables extends TestCase{
	private $usedCSS;
	private $database;

	public function setUp() : void {
		parent::setUp();

		if ( $this->isPHP8() ) {
			return;
		}

		$this->usedCSS = $this->getMockBuilder( UsedCSS::class )
			->disableOriginalConstructor()
			->getMock();
		$this->database  = new Database( $this->usedCSS );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input ){
		if ( $this->isPHP8() ) {
			$this->assertTrue(true);
			return;
		}

		$this->usedCSS->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( $input['usedCSS']['exists'] ) );

		if ( true === $input['usedCSS']['exists'] ) {
			$this->usedCSS->expects( $this->once() )
				->method('uninstall');
		}

		$this->database->drop_rucss_database_tables();
	}

	/**
	 * Check if is PHP8.
	 *
	 * @return bool
	 */
	public function isPHP8() {
		$version = explode('.', PHP_VERSION);
		if ( $version[0] >= 8 ) {
			$this->assertTrue(true);
			return true;
		}

		return false;
	}
}
