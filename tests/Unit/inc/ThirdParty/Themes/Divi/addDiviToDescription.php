<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
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
	 * @dataProvider providerTestData
	 */
	public function testAddDiviToDescription( $config, $expected ) {
		$options_api = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$options     = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$theme       = new WP_Theme( $config['theme-name'], 'wp-content/themes/' );
		$theme->set_name( $config['theme-name'] );

		if ( $config['theme-template'] ) {
			$theme->set_template( $config['theme-template'] );
		}

		Functions\when( 'wp_get_theme' )->justReturn( $theme );

		$divi = new Divi( $options_api, $options );

		$this->assertSame( $expected, $divi->add_divi_to_description( $config['disabled-items'] ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'addDiviToDescription' );
	}

}
