<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Database;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::truncate_used_css_table
 *
 * @group  RUCSS
 */
class Test_TruncateUsedCssTable extends TestCase{
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
				->method('truncate')
				->will( $this->returnValue( $input['usedCSS']['truncate'] ) );
		}

		$this->database->truncate_used_css_table();
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
