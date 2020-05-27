<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Settings;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CriticalPath\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\AdminTrait;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Settings::set_async_css_mobile_default_value
 *
 * @group  CriticalPath
 * @group  CriticalPathSettings
 */
class Test_SetAsyncCssMobileDefaultValue extends TestCase {
    use AdminTrait;

    private $settings;

	public function setUp() {
        parent::setUp();

        $this->setUpMocks();

		$this->settings = new Settings(
			$this->options,
			$this->beacon,
			$this->critical_css,
			'wp-content/plugins/wp-rocket/views/cpcss'
		);
    }

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldUpdateOption( $versions, $update ) {
        if ( true === $update ) {
            $options_value = [
                'async_css_mobile' => 1,
            ];

            $this->options->shouldReceive( 'set' )
                ->once()
                ->with( 'async_css_mobile', 0 );

            $this->options->shouldReceive( 'get_options' )
                ->once()
                ->andReturn( $options_value );

            Functions\expect( 'update_option' )
                ->once()
                ->with( 'wp_rocket_settings', $options_value );
        } else {
            Functions\expect( 'update_option' )->never();
        }

        $this->settings->set_async_css_mobile_default_value( $versions['new'], $versions['old'] );
	}
}
