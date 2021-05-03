<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Deactivation\DeactivationIntent;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent;

/**
 * @covers \WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent::activate_safe_mode
 * @group  DeactivationIntent
 */
class Test_ActivateSafeMode extends TestCase {
	private $deactivation;
	private $options;
	private $options_api;

	public function setUp() : void {
		parent::setUp();

		$this->options_api  = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$this->options      = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$this->deactivation = new DeactivationIntent( Mockery::mock( 'WP_Rocket\Admin\Deactivation\Render' ), $this->options_api, $this->options );

		Functions\when( 'check_ajax_referer' )->justReturn( true );
	}

	public function testShouldDoNothingWhenNoCapacity() {
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\expect( 'wp_send_json_error' )->once();
		Functions\expect( 'wp_send_json_success' )->never();

		$this->deactivation->activate_safe_mode();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldResetOptions( $expected ) {
		Functions\when( 'current_user_can' )->justReturn( true );

		$this->options->shouldReceive( 'set_values' )
			->once()
			->with( $expected );
		$this->options->shouldReceive( 'get_options' )
			->once()
			->andReturn( $expected );
		$this->options_api->shouldReceive( 'set' )
			->once()
			->with( 'settings', $expected );

		Functions\expect( 'wp_send_json_success' )->once();

		$this->deactivation->activate_safe_mode();
	}
}
