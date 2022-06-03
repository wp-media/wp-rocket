<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\UnlimitedElements;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Plugins\UnlimitedElements;

/**
 * @covers  \WP_Rocket\ThirdParty\Plugins\UnlimitedElements::exclude_inline_from_rucss
 * @group   UnlimitedElements
 */
class Test_ExcludeInlineFromRucss extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
        $unlimited_elements = new UnlimitedElements();
		$this->assertSame( $expected, $unlimited_elements->exclude_inline_from_rucss($config['excluded'] ) );
	}

}
