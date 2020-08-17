<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\CapTrait;
use WPMedia\PHPUnit\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::restore_defaults
 *
 * @group  DelayJS
 */
class Test_RestoreDefaults extends AjaxTestCase {

	protected $path_to_test_data = '/inc/Engine/Optimization/DelayJS/Admin/Subscriber/restoreDefaults.php';
	protected $action = 'rocket_restore_delay_js_defaults';

	public function setUp() {
		parent::setUp();

		CapTrait::hasAdminCapBeforeClass();
		CapTrait::setAdminCap();
	}

	public function tearDown() {
		parent::tearDown();

		CapTrait::resetAdminCap();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $result, $success ) {
		if ( false !== $result ) {
			$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		}else{
			$user_id = static::factory()->user->create( [ 'role' => 'editor' ] );
		}

		wp_set_current_user( $user_id );

		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );
		$actual = $this->callAjaxAction();

		if ( false !== $success ) {
			$this->assertTrue( $actual->success );
			$this->assertSame(
				$result,
				$actual->data
			);
		}else{
			$this->assertFalse( $actual->success );
		}
	}

	public function providerTestData() {
		$fixture = require WP_ROCKET_TESTS_FIXTURES_DIR . $this->path_to_test_data;

		return $fixture;
	}
}
