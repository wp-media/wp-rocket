<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Settings::set_async_css_mobile_default_value
 *
 * @group  CriticalPath
 * @group  CriticalPathSettings
 */
class Test_SetAsyncCssMobileDefaultValue extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldUpdateOption( $versions, $update ) {
		$options  = Mockery::mock( Options_Data::class );
		$settings = new Settings(
			$options,
			Mockery::mock( Beacon::class ),
			Mockery::mock( CriticalCSS::class ),
			'wp-content/cache/critical-css/',
			'wp-content/plugins/wp-rocket/views/cpcss'
		);

        if ( true === $update ) {
            $options_value = [
                'async_css_mobile' => 1,
            ];

            $options->shouldReceive( 'set' )
                ->once()
                ->with( 'async_css_mobile', 0 );

            $options->shouldReceive( 'get_options' )
                ->once()
                ->andReturn( $options_value );

            Functions\expect( 'update_option' )
                ->once()
                ->with( 'wp_rocket_settings', $options_value );
        } else {
            Functions\expect( 'update_option' )->never();
        }

        $settings->set_async_css_mobile_default_value( $versions['new'], $versions['old'] );
	}
}
