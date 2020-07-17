<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Mockery;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\ThirdParty\Themes\Divi;
use \WP_Theme;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Divi::maybe_disable_youtube_preview
 * @uses   ::is_divi
 *
 * @group  ThirdParty
 */
class Test_MaybeDisableYoutubePreview extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/maybeDisableYoutubePreview.php';

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testSetsCorrectOptions( $config, $expected ) {
		$theme = new WP_Theme( $config['stylesheet'], 'wp-content/themes/' );
		$theme->set_name( $config['stylesheet'] );

		if ( isset( $config['template'] ) ) {
			$theme->set_template( $config['template'] );
		}

		Functions\when( 'wp_get_theme' )->justReturn( $theme );

		$options = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$options->shouldReceive( 'set' )
		        ->times( $config['set-lazy'] )
		        ->with( 'lazyload_youtube', 0 );
		$options->shouldReceive( 'get_options' )
		        ->times( $config['set-lazy'] )
		        ->andReturn( [ 'lazyload_youtube' => 0, ] );

		$options_api = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$options_api->shouldReceive( 'set' )
		            ->times( $config['set-lazy'] )
		            ->with( 'settings', [ 'lazyload_youtube' => 0, ] )
		            ->andReturn( $expected );

		$divi = new Divi( $options_api, $options );
		$divi->maybe_disable_youtube_preview( $config['stylesheet'], $theme );
	}
}
