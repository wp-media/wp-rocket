<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Flatsome;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Themes\Flatsome;

/**
 * @covers  \WP_Rocket\ThirdParty\Themes\Flatsome::preserve_patterns
 */
class Test_PreservePatterns extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
        $flatsome = new Flatsome();
		$this->assertSame( $expected, $flatsome->preserve_patterns($config['excluded'] ) );
	}

}
