<?php

namespace WP_Rocket\Tests\Integration\Inc\Engine\Common\Performance\AJAX\Subscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * Test class covering WP_Rocket\Engine\Common\PerformanceHints\AJAX\Subscriber::add_nonce
 *
 * @group PerformanceHints
 */
class Test_AddNonce extends AjaxTestCase {

	public function set_up() {
		parent::set_up();

		$this->action = 'rocket_beacon_nonce';
	}

	public function tear_down() {
		wp_set_current_user( 0 );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnFreshNonce( $config, $expected ) {
		// Logged-in requests hit wp_ajax_rocket_beacon_nonce, logged-out ones wp_ajax_nopriv_rocket_beacon_nonce.
		wp_set_current_user(
			$config['logged_in'] ? self::factory()->user->create( [ 'role' => 'subscriber' ] ) : 0
		);

		$result = $this->callAjaxAction();

		$this->assertSame( $expected['success'], $result->success );
		$this->assertNotEmpty( $result->data->nonce );

		// The returned nonce must be accepted by the beacon endpoints.
		$this->assertNotFalse( wp_verify_nonce( $result->data->nonce, 'rocket_beacon' ) );
	}
}
