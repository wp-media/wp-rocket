<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Uncode;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Uncode;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Uncode::exclude_delay_js
 * @uses   ::rocket_get_constant()
 *
 * @group  ThirdParty
 */
class Test_ExcludeDelayJS extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testExcludeJS( $config, $expected ) {
		Functions\when( 'get_template_directory_uri' )->justReturn('/wp-content/themes/uncode' );
		Functions\when( 'wp_parse_url' )->returnArg();

		$uncode = new Uncode();

		$this->assertSame( $expected, $uncode->exclude_delay_js($config['exclusions']) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'excludeDelayJs' );
	}
}
