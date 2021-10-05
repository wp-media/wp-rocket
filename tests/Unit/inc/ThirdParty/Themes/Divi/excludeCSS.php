<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Divi;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Divi::exclude_divi_css_from_combine
 * @uses   ::rocket_get_constant()
 *
 * @group  ThirdParty
 */
class Test_ExcludeCSS extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testExcludeCSS( $config, $expected ) {
		$options_api = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$options     = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$delayjs_html     = Mockery::mock( 'WP_Rocket\Engine\Optimization\DelayJS\HTML' );

		$options->shouldReceive( 'get' )
		        ->once()
		        ->with( 'minify_concatenate_css', 0 )
		        ->andReturn( $config['minify_concatenate_css'] );
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
		Functions\when( 'wp_parse_url' )->justReturn('/wp-content/' );

		$divi = new Divi( $options_api, $options, $delayjs_html );

		$this->assertSame( $expected, $divi->exclude_divi_css_from_combine( $config['excluded-paths'] ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'excludeCSS' );
	}
}
