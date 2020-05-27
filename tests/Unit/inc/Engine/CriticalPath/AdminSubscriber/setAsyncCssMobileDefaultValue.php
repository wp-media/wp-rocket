<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Engine\CriticalPath\AdminSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::set_async_css_mobile_default_value
 *
 * @group  CriticalPath
 */
class Test_SetAsyncCssMobileDefaultValue extends TestCase {

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldUpdateOption( $versions, $update ) {
		$options = Mockery::mock( Options_Data::class );
		$subscriber = new AdminSubscriber(
			$options,
			Mockery::mock( Beacon::class ),
			Mockery::mock( CriticalCSS::class ),
			Mockery::mock( ProcessorService::class ),
			'wp-content/cache/critical-css/',
			'wp-content/plugins/wp-rocket/views/cpcss'
		);

        if ( true === $update ) {
            $settings = [
                'async_css_mobile' => 1,
            ];

            $options->shouldReceive( 'set' )
                ->once()
                ->with( 'async_css_mobile', 0 );

            $options->shouldReceive( 'get_options' )
                ->once()
                ->andReturn( $settings );

            Functions\expect( 'update_option' )
                ->once()
                ->with( 'wp_rocket_settings', $settings );
        } else {
            Functions\expect( 'update_option' )->never();
        }

        $subscriber->set_async_css_mobile_default_value( $versions['new'], $versions['old'] );
	}
}
