<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJs\Admin\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber;
use WP_Rocket\ServiceProvider\Options;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::set_option_on_update
 *
 * @group  DelayJs
 */
class Test_SetOptionOnUpdate extends TestCase{

	public function tearDown() {
		parent::tearDown();

		$options = new \WP_Rocket\Admin\Options( 'wp_rocket_' );
		$options->set( 'settings', null );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $old_version, $valid_version ){

		do_action( 'wp_rocket_upgrade', '', $old_version );

		$options      = new \WP_Rocket\Admin\Options( 'wp_rocket_' );
		$option_array = new Options_Data( $options->get( 'settings' ) );

		if ( $valid_version ) {
			$this->assertEquals( $option_array->get('delay_js', null), 0 );
		} else {
			$this->assertNull( $option_array->get('delay_js', null) );
		}

	}

}
