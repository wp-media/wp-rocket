<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJs\Admin\Subscriber;

use WP_Rocket\Tests\Integration\CapTrait;
use WPMedia\PHPUnit\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::restore_defaults
 *
 * @group  DelayJs
 */
class Test_RestoreDefaults extends AjaxTestCase{

	protected $path_to_test_data = '/inc/Engine/Optimization/DelayJs/Admin/Subscriber/restoreDefaults.php';
	protected $action = 'rocket_restore_delay_js_defaults';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $input, $restored ){
		$capabilities = isset( $input['capabilities'] ) ? $input['capabilities'] : [] ;

		if ( in_array('rocket_manage_options', $capabilities) ){
			CapTrait::setAdminCap();

			//create an editor user that has the capability
			$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		}else{
			//create an editor user that has no capability
			$user_id = static::factory()->user->create( [ 'role' => 'editor' ] );
		}

		wp_set_current_user( $user_id );

		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );
		$actual = $this->callAjaxAction();

		if ( $restored ){
			$this->assertTrue( $actual->success );
		}else{
			$this->assertFalse( $actual->success );
		}

	}

	public function providerTestData() {
		$fixture = require WP_ROCKET_TESTS_FIXTURES_DIR . $this->path_to_test_data;

		return $fixture;
	}

}
