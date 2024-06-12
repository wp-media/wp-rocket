<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Jevelin;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Jevelin;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Jevelin::preserve_patterns
 * @group Jevelin
 * @group ThirdParty
 */
class Test_PreservePatterns extends TestCase {

	protected function setUp(): void{
		parent::setUp();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

        $jevelin = new Jevelin();
        $result = $jevelin->preserve_patterns( $config['patterns'] );

		$this->assertSame( $expected, $result );
	}
}
