<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Uncode;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Uncode;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Uncode::exclude_js
 *
 * @group Uncode
 * @group ThirdParty
 */
class Test_ExcludeJS extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testExcludeJS( $config, $expected ) {
		Functions\when( 'get_template_directory_uri' )->justReturn( '/wp-content/themes/uncode' );
		Functions\when( 'wp_parse_url' )->returnArg();

		$uncode = new Uncode();

		$this->assertSame(
			$expected,
			$uncode->exclude_js( $config['exclusions'] )
		);
	}
}
