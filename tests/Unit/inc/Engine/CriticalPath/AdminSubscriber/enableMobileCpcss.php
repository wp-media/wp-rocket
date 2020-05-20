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
		Functions\when( 'current_user_can' )->justReturn( $config['rocket_manage_options'] );

		if ( ! $update ) {
			Functions\expect( 'wp_send_json_error' )->once();
			$this->options->shouldReceive( 'set' )->never();
		} else {
			$this->options->shouldReceive( 'get_options' )->andReturn( $this->options );
			$this->options->shouldReceive( 'set' )->with( 'async_css_mobile', 1 );

			Functions\expect( 'update_option' )->with( 'wp_rocket_settings', $this->options )->once();
			Functions\expect( 'wp_send_json_success' )->once();
		}

		$this->subscriber->enable_mobile_cpcss();
	}
}
