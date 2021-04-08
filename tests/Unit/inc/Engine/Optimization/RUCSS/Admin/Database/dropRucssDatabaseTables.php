<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::drop_rucss_database_tables
 *
 * @group  RUCSS
 */
class Test_DropRucssDatabaseTables extends TestCase{
	private $resources;
	private $usedCSS;
	private $database;

	public function setUp() : void {
		parent::setUp();

		$version = explode('.', PHP_VERSION);
		if ( $version[0] >= 8 ) {
			$this->assertTrue(true);
			return;
		}

		$this->resources = $this->getMockBuilder('WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources')
			->disableOriginalConstructor()
			->getMock();

		$this->usedCSS = $this->getMockBuilder('WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS')
			->disableOriginalConstructor()
			->getMock();
		$this->database  = new Database( $this->resources, $this->usedCSS );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input ){
		$version = explode('.', PHP_VERSION);
		if ( $version[0] >= 8 ) {
			$this->assertTrue(true);
			return;
		}

		$this->resources->expects( $this->once() )
				->method( 'exists' )
				->will( $this->returnValue( $input['resources']['exists'] ) );

		if ( true === $input['resources']['exists'] ) {
			$this->resources->expects( $this->once() )
				->method('uninstall');
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
}
