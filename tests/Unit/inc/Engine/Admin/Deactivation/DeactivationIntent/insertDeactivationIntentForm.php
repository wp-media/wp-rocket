<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Deactivation\DeactivationIntent;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent;

/**
 * @covers \WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent::insert_deactivation_intent_form
 * @group  DeactivationIntent
 */
class Test_InsertDeactivationIntentForm extends TestCase {
	private $deactivation;
	private $options;
	private $options_api;

	protected function set_up() {
		parent::set_up();

		$this->options_api  = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$this->options      = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$this->deactivation = Mockery::mock(
			DeactivationIntent::class . '[generate]',
			[
				'views/deactivation-intent',
				$this->options_api,
				$this->options
			]
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'get_option' )->justReturn( $config['option'] );
		Functions\when( 'get_transient' )->justReturn( $config['transient'] );
		Functions\when( 'admin_url' )->alias( function( $path ) {
			return 'http://example.org/wp-admin/' . $path;
		} );

		if ( ! $expected ) {
			$this->deactivation->shouldReceive( 'generate' )
				->never();
		} else {
			$this->deactivation->shouldReceive( 'generate' )
				->once()
				->with(
					'form',
					[
						'form_action' => 'http://example.org/wp-admin/admin-post.php?action=rocket_deactivation',
					]
				)
				->andReturn( '' );
			$this->expectOutputString( '' );
		}

		$this->deactivation->insert_deactivation_intent_form();
	}
}
