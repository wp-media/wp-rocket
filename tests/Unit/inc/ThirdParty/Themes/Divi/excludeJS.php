<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Divi;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Divi::exclude_js
 * @uses   ::rocket_get_constant()
 *
 * @group  ThirdParty
 */
class Test_ExcludeJS extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testExcludeJS( $config, $expected ) {
		$options_api = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$options     = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$delayjs_html     = Mockery::mock( 'WP_Rocket\Engine\Optimization\DelayJS\HTML' );
		$used_css     = Mockery::mock( UsedCSS::class );

		Functions\when( 'rocket_get_constant' )->justReturn( $config['builder-constant'] );
		Functions\when( 'home_url' )->justReturn('https://example.com' );

		$divi = new Divi( $options_api, $options, $delayjs_html, $used_css );

		$this->assertSame( $expected, $divi->exclude_js( $config['excluded-paths'] ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'excludeJS' );
	}
}
