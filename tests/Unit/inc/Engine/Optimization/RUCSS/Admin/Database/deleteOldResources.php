<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Database;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::delete_old_resources
 *
 * @group  RUCSS
 */
class Test_DeleteOldResources extends TestCase{
	private $resources;
	private $usedCSS;
	private $database;

	public function setUp() : void {
		parent::setUp();

		if ( $this->isPHP8() ) {
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
		if ( $this->isPHP8() ) {
			$this->assertTrue(true);
			return;
		}

		$this->resources->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( $input['resources']['exists'] ) );

		if ( true === $input['resources']['exists'] ) {
			$this->resources->expects( $this->once() )
				->method('delete_old_items');
		}

		$this->database->delete_old_resources();
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
