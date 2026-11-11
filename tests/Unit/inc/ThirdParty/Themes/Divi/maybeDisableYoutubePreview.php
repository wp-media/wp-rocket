<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Mockery;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Divi::maybe_disable_youtube_preview
 *
 * @group ThirdParty
 */
class Test_MaybeDisableYoutubePreview extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testSetsCorrectOptions( $config, $expected ) {
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

		$divi->maybe_disable_youtube_preview();
	}
}
