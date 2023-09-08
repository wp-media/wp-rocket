<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Divi;
use WP_Theme;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Divi::add_divi_to_description
 * @uses   \WP_Rocket\ThirdParty\Themes\Divi::is_divi
 *
 * @group  ThirdParty
 */
class Test_AddDiviToDescription extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testAddDiviToDescription( $config, $expected ) {
		$options_api  = Mockery::mock( Options::class );
		$options      = Mockery::mock( Options_Data::class );
		$delayjs_html = Mockery::mock( HTML::class );
		$used_css     = Mockery::mock( UsedCSS::class );
		$theme        = new WP_Theme( $config['theme-name'], 'wp-content/themes/' );
		$theme->set_name( $config['theme-name'] );

		if ( $config['theme-template'] ) {
			$theme->set_template( $config['theme-template'] );
		}

		Functions\when( 'wp_get_theme' )->justReturn( $theme );

		$divi = new Divi( $options_api, $options , $delayjs_html, $used_css );

		$this->assertSame( $expected, $divi->add_divi_to_description( $config['disabled-items'] ) );
	}
}
