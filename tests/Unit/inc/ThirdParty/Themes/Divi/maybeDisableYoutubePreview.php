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
 * @covers \WP_Rocket\ThirdParty\Themes\Divi::maybe_disable_youtube_preview
 * @uses   \WP_Rocket\ThirdParty\Themes\Divi::is_divi
 *
 * @group  ThirdParty
 */
class Test_MaybeDisableYoutubePreview extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testSetsCorrectOptions( $config, $expected ) {
		$theme = new WP_Theme( $config['stylesheet'], 'wp-content/themes/' );
		$theme->set_name( $config['stylesheet'] );

		if ( isset( $config['template'] ) ) {
			$theme->set_template( $config['template'] );
		}

		Functions\when( 'wp_get_theme' )->justReturn( $theme );

		$options_api  = Mockery::mock( Options::class );
		$options      = Mockery::mock( Options_Data::class );
		$delayjs_html = Mockery::mock( HTML::class );
		$used_css     = Mockery::mock( UsedCSS::class );

		$options->shouldReceive( 'set' )
		        ->times( $config['set-lazy'] )
		        ->with( 'lazyload_youtube', 0 );
		$options->shouldReceive( 'get_options' )
		        ->times( $config['set-lazy'] )
		        ->andReturn( [ 'lazyload_youtube' => 0, ] );
		$options_api->shouldReceive( 'set' )
		            ->times( $config['set-lazy'] )
		            ->with( 'settings', [ 'lazyload_youtube' => 0, ] )
		            ->andReturn( $expected );

		$divi = new Divi( $options_api, $options, $delayjs_html, $used_css );

		$divi->maybe_disable_youtube_preview( $config['stylesheet'], $theme );
	}
}
