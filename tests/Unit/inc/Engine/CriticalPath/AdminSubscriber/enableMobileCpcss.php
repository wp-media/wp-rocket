<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::enable_mobile_cpcss
 * @uses   ::rocket_get_constant
 *
 * @group  CriticalPath
 */
class Test_EnableMobileCpcss extends TestCase {
	use GenerateTrait;

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'check_ajax_referer' )->justReturn( true );

		$this->setUpMocks();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEnableMobileCpcss( $config, $update ) {
		Functions\expect( 'current_user_can' )->once()->with( 'rocket_manage_options' )->andReturn( $config['rocket_manage_options'] );
		if ( isset( $config['rocket_regenerate_critical_css']) ) {
			Functions\expect( 'current_user_can' )->once()->with( 'rocket_regenerate_critical_css' )->andReturn( $config['rocket_regenerate_critical_css'] );
		}

		if ( ! $update ) {
			Functions\expect( 'wp_send_json_error' )->once();
			$this->options->shouldReceive( 'set' )->never();
		} else {
			$this->options->shouldReceive( 'get_options' )->andReturn( $this->options );
			$this->options->shouldReceive( 'set' )->with( 'async_css_mobile', 1 );

			$this->critical_css->shouldReceive( 'process_handler' )->with( 'mobile' );

			Functions\expect( 'update_option' )->with( 'wp_rocket_settings', $this->options )->once();
			Functions\expect( 'wp_send_json_success' )->once();
		}

		$this->subscriber->enable_mobile_cpcss();
	}
}
