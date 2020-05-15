<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\AdminSubscriber;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::set_async_css_mobile_default_value
 * @group  CriticalPath
 */
class Test_SetAsyncCssMobileDefaultValue extends TestCase {
	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->beacon     = Mockery::mock( Beacon::class );
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new AdminSubscriber(
			$this->options,
			$this->beacon,
			'wp-content/cache/critical-css/',
			'wp-content/plugins/wp-rocket/views/metabox/cpcss'
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldUpdateOption( $versions, $update ) {
        if ( true === $update ) {
            $options = [
                'async_css_mobile' => 1,
            ];

            $this->options->shouldReceive( 'set' )
                ->once()
                ->with( 'async_css_mobile', 0 );

            $this->options->shouldReceive( 'get_options' )
                ->once()
                ->andReturn( $options );

            Functions\expect( 'update_option' )
                ->once()
                ->with( 'wp_rocket_settings', $options );
        } else {
            Functions\expect( 'update_option' )->never();
        }

        $this->subscriber->set_async_css_mobile_default_value( $versions['new'], $versions['old'] );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'setAsyncCssMobileDefaultValue' );
	}
}
