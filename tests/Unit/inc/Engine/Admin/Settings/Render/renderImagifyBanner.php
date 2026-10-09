<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Render;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Model\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Render::render_imagify_banner
 *
 * @group SettingsPage
 */
class Test_RenderImagifyBanner extends TestCase {
	/**
	 * @dataProvider configTestData
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldRenderExpectedBanner( $config, $expected ) {
		$args    = [ 'title' => 'Imagify' ];
		$partner = Mockery::mock( 'alias:Imagify_Partner' );
		$partner->shouldReceive( 'is_imagify_activated' )->andReturn( $config['activated'] );

		Functions\when( 'rocket_get_constant' )->justReturn( $config['white_label'] );

		$render = Mockery::mock( Render::class, [ 'vfs://views', Mockery::mock( PluginFamily::class ) ] )->makePartial();

		if ( null === $expected['template'] ) {
			$render->shouldReceive( 'generate' )->never();
		} else {
			$expected_args = $args;

			if ( ! $config['activated'] ) {
				$render->shouldReceive( 'get_imagify_plugin_data' )->once()->andReturn( [] );
				$expected_args['plugin_data'] = [];
			}

			$render->shouldReceive( 'generate' )
				->once()
				->with( $expected['template'], $expected_args )
				->andReturn( 'banner' );
		}

		ob_start();
		$render->render_imagify_banner( $args );

		$this->assertSame( null === $expected['template'] ? '' : 'banner', ob_get_clean() );
	}
}
