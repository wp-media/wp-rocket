<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Divi::exclude_css_from_combine
 *
 * @group  ThirdParty
 */
class Test_ExcludeCSSFromCombine extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$options_api  = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$options      = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$delayjs_html = Mockery::mock( 'WP_Rocket\Engine\Optimization\DelayJS\HTML' );
		$used_css     = Mockery::mock( UsedCSS::class );

		$options->shouldReceive( 'get' )
		        ->once()
		        ->with( 'minify_concatenate_css', 0 )
		        ->andReturn( $config['minify_concatenate_css'] );
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content/' );
		Functions\when( 'wp_parse_url' )->justReturn( '/wp-content/' );

		$divi = new Divi( $options_api, $options, $delayjs_html, $used_css );

		$this->assertSame(
			$expected,
			$divi->exclude_css_from_combine( $config['excluded-paths'] )
		);
	}
}
