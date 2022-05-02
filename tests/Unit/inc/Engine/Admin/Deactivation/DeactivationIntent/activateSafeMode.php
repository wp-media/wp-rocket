<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Deactivation\DeactivationIntent;

use Mockery;
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
		$this->deactivation = new DeactivationIntent( '', $this->options_api, $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldResetOptions( $expected ) {
		$this->options->shouldReceive( 'set_values' )
			->once()
			->with( $expected );
		$this->options->shouldReceive( 'get_options' )
			->once()
			->andReturn( $expected );
		$this->options_api->shouldReceive( 'set' )
			->once()
			->with( 'settings', $expected );

		$this->deactivation->activate_safe_mode();
	}
}
