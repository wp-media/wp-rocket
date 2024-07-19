<?php

namespace WP_Rocket\Tests\Integration\Inc\Engine\Common\Performance\AJAX\Subscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * Test class covering WP_Rocket\Engine\Common\PerformanceHints\AJAX\Subscriber::add_data
 *
 * @group PerformanceHints
 */
class Test_AddData extends AjaxTestCase {
	private $allowed;

	public function set_up() {
		parent::set_up();

		self::installAtfTable();

		$this->action = 'rocket_lcp';
	}

	/**
	 * $_POST is cleared in parent method
	 *
	 * @return void
	 */
	public function tear_down() {
		self::uninstallAtfTable();

		remove_filter( 'rocket_above_the_fold_optimization', [ $this, 'set_allowed' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$_POST['rocket_lcp_nonce'] = wp_create_nonce( 'rocket_lcp' );
		$_POST['action']           = 'rocket_lcp';
		$_POST['url']              = $config['url'];
		$_POST['is_mobile']        = $config['is_mobile'];
		$_POST['images']           = $config['images'];
		$_POST['status']           = $config['status'] ?? 'success';

		$this->allowed = $config['filter'];

		add_filter( 'rocket_above_the_fold_optimization', [ $this, 'set_allowed' ] );

		$result = $this->callAjaxAction();

		$this->assertSame( $expected['result'], $result->success );
	}

	public function set_allowed() {
		return $this->allowed;
	}
}
