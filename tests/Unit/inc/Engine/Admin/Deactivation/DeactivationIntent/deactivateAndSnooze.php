<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Deactivation\DeactivationIntent;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent::deactivate_and_snooze
 * @group  DeactivationIntent
 */
class Test_DeactivateAndSnooze extends TestCase {
	private $deactivation;
	private $options;
	private $options_api;

	protected function set_up() {
		parent::set_up();

		$this->options_api  = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$this->options      = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$this->deactivation = new DeactivationIntent(
			'views/deactivation-intent',
			$this->options_api,
			$this->options
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config ) {
		if ( 0 === $config['snooze'] ) {
			Functions\expect( 'add_option' )
				->with( 'wp_rocket_hide_deactivation_form', 1 )
				->once();
		} else {
			Functions\expect( 'set_transient' )
				->with( 'rocket_hide_deactivation_form', $config['snooze'] * 86400 )
				->once();
		}

		Functions\expect( 'deactivate_plugins' )
			->with( 'wp-rocket/wp-rocket.php' )
			->once();

		$this->deactivation->deactivate_and_snooze( $config['snooze'] );
	}
}
