<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Deactivation\DeactivationIntent;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent;

/**
 * @covers \WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent::activate_safe_mode
 * @group  DeactivationIntent
 */
class Test_ActivateSafeMode extends TestCase {
	private $deactivation;
	private $options;
	private $options_api;

	public function setUp() {
		parent::setUp();

		$this->options_api  = $this->createMock( 'WP_Rocket\Admin\Options' );
		$this->options      = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$this->deactivation = new DeactivationIntent( $this->createMock( 'WP_Rocket\Admin\Deactivation\Render' ), $this->options_api, $this->options );

		Functions\when( 'check_ajax_referer' )->justReturn( true );
	}

	public function testShouldDoNothingWhenNoCapacity() {
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\expect( 'wp_send_json_error' )->once();
		Functions\expect( 'wp_send_json_success' )->never();

		$this->deactivation->activate_safe_mode();
	}

	public function testShouldResetOptions() {
		$options = [
			'embeds'                 => 0,
			'defer_all_js'           => 0,
			'async_css'              => 0,
			'lazyload'               => 0,
			'lazyload_iframes'       => 0,
			'lazyload_youtube'       => 0,
			'minify_css'             => 0,
			'minify_concatenate_css' => 0,
			'minify_js'              => 0,
			'minify_concatenate_js'  => 0,
			'minify_html'            => 0,
			'minify_google_fonts'    => 0,
			'cdn'                    => 0,
		];

		Functions\when( 'current_user_can' )->justReturn( true );

		$this->options->expects( $this->once() )
			->method( 'set_values' )
			->with( $options );
		$this->options->expects( $this->once() )
			->method( 'get_options' )
			->willReturn( $options );
		$this->options_api->expects( $this->once() )
			->method( 'set' )
			->with( 'settings', $options );

		Functions\expect( 'wp_send_json_success' )->once();

		$this->deactivation->activate_safe_mode();
	}
}
