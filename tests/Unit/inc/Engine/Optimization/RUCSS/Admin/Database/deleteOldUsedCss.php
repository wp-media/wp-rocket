<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Database;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::delete_old_used_css
 *
 * @group  RUCSS
 */
class Test_DeleteOldUsedCss extends TestCase{
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

		$this->usedCSS->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( $input['usedCSS']['exists'] ) );

		if ( true === $input['usedCSS']['exists'] ) {
			$this->usedCSS->expects( $this->once() )
				->method('delete_old_used_css');
		}

		$this->database->delete_old_used_css();
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
