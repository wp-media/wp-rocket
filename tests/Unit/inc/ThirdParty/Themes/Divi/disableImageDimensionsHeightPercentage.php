<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Themes\Divi;
use WP_Theme;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Divi::disable_image_dimensions_height_percentage
 *
 * @group  ThirdParty
 */
class Test_DisableImageDimensionsHeightPercentage extends TestCase {
	/**
	 * @dataProvider providerTestData
	 */
	public function testAddDiviToDescription( $config, $expected ) {
		$options_api = Mockery::mock( Options::class );
		$options     = Mockery::mock( Options_Data::class );
		$delayjs_html     = Mockery::mock( 'WP_Rocket\Engine\Optimization\DelayJS\HTML' );
		$used_css     = Mockery::mock( UsedCSS::class );
		$theme = Mockery::mock('WP_Theme');
		$theme->shouldReceive('get_template')->andReturn($config['theme-template']);
		$theme->shouldReceive('get_stylesheet')->andReturn($config['theme-name']);

		Functions\when( 'wp_get_theme' )->justReturn( $theme );

		$divi = new Divi( $options_api, $options, $delayjs_html, $used_css );

		$this->assertSame( $expected, array_values( $divi->disable_image_dimensions_height_percentage( $config['images'] ) ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'disableImageDimensionsHeightPercentage' )['test_data'];
	}
}
