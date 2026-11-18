<?php

namespace WP_Rocket\Tests\Integration\Inc\Engine\Common\Performance\AJAX\Subscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;
use WPAjaxDieContinueException;

/**
 * Test class covering WP_Rocket\Engine\Common\PerformanceHints\AJAX\Subscriber::add_nonce
 *
 * @group PerformanceHints
 */
class Test_AddNonce extends AjaxTestCase {

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Same table lifecycle as the sibling AJAX tests: a test class that leaves the ATF table
		// uninstalled while its exists() check stays cached breaks Test_CheckData, which runs after it.
		self::installAtfTable();
	}

	public static function tear_down_after_class() {
		self::uninstallAtfTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->action = 'rocket_beacon_nonce';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnFreshNonce( $config, $expected ) {
		wp_set_current_user(
			$config['logged_in'] ? self::factory()->user->create( [ 'role' => 'subscriber' ] ) : 0
		);

		$result = $config['logged_in'] ? $this->callAjaxAction() : $this->callNoprivAjaxAction();

		$this->assertSame( $expected['success'], $result->success );
		$this->assertNotEmpty( $result->data->nonce );

		// The returned nonce must be accepted by the beacon endpoints.
		$this->assertNotFalse( wp_verify_nonce( $result->data->nonce, 'rocket_beacon' ) );
	}

	/**
	 * Fires the logged-out hook: WP_Ajax_UnitTestCase::_handleAjax() only triggers wp_ajax_{action}.
	 *
	 * @return mixed the response.
	 */
	private function callNoprivAjaxAction() {
		ob_start();

		try {
			do_action( 'wp_ajax_nopriv_' . $this->action );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		return $this->getResponse();
	}
}
