<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Deactivation\DeactivationIntent;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent::add_modal_assets
 * @group  DeactivationIntent
 */
class Test_AddModalAssets extends TestCase {
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
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'get_option' )->justReturn( $config['option'] );
		Functions\when( 'get_transient' )->justReturn( $config['transient'] );

		if ( ! $expected ) {
			Functions\expect( 'wp_enqueue_style' )->never();
			Functions\expect( 'wp_enqueue_script' )->never();
			Functions\expect( 'wp_add_inline_script' )->never();
		} else {
			Functions\expect( 'wp_enqueue_style' )->once();
			Functions\expect( 'wp_enqueue_script' )->once();
			Functions\expect( 'wp_add_inline_script' )->once();
		}

		$this->deactivation->add_modal_assets( $config['hook'] );
	}
}
