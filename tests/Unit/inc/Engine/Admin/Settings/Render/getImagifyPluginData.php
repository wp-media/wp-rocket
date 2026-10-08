<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Render;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Model\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Render::get_imagify_plugin_data
 *
 * @group SettingsPage
 */
class Test_GetImagifyPluginData extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_imagify_plugin_data' )
			->andReturn( $config['transient'] );

		if ( $expected['calls_api'] ) {
			Functions\expect( 'plugins_api' )
				->once()
				->with( 'plugin_information', Mockery::on( function ( $args ) {
					return 'imagify' === $args['slug'];
				} ) )
				->andReturn( $config['api_result'] );
			Functions\expect( 'is_wp_error' )
				->once()
				->andReturn( $config['is_error'] );
			Functions\expect( 'set_transient' )
				->once()
				->with( 'rocket_imagify_plugin_data', $expected['saved'], WEEK_IN_SECONDS );
		} else {
			Functions\expect( 'plugins_api' )->never();
			Functions\expect( 'set_transient' )->never();
		}

		$render = new Render( 'vfs://public/wp-content/plugins/wp-rocket/views/settings', Mockery::mock( PluginFamily::class ) );

		$this->assertEquals( $expected['data'], $render->get_imagify_plugin_data() );
	}
}
