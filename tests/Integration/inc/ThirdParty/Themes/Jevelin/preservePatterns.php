<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes;

use WP_Rocket\Tests\Integration\TestCase;
/**
 * @covers \WP_Rocket\ThirdParty\Themes\Jevelin::preserve_patterns
 * @group Jevelin
 * @group ThirdParty
 */
class Test_PreservePatterns extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

        $result = apply_filters( 'rocket_rucss_inline_content_exclusions', $config['patterns'] );

		$this->assertSame( $expected, $result );
	}
}
