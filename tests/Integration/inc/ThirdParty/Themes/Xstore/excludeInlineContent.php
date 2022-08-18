<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Xstore;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Themes\Xstore;

/**
 * @covers  \WP_Rocket\ThirdParty\Themes\Xstore::exclude_inline_content
 */
class Test_ExcludeInlineContent extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
        $xstore = new Xstore();
		$this->assertSame( $expected, $xstore->exclude_inline_content($config['excluded'] ) );
	}

}
