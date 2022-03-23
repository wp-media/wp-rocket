<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Enfold;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Enfold::exclude_js()
 *
 * @group  ThirdParty
 */
class Test_ExcludeJS extends TestCase {

	public function setUp() : void {
		parent::setUp();

		Functions\when( 'avia_lang_setup' )->justReturn();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertEquals($expected['excluded'], apply_filters( 'rocket_delay_js_exclusions', $config['excluded']));
	}
}
