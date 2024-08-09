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

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install in set_up_before_class because of exists().
		self::installAtfTable();
	}

	public static function tear_down_after_class() {
		self::uninstallAtfTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->action = 'rocket_beacon';
	}

	/**
	 * $_POST is cleared in parent method
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_filter( 'rocket_above_the_fold_optimization', [ $this, 'set_allowed' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$_POST['rocket_beacon_nonce'] = wp_create_nonce( 'rocket_beacon' );
		$_POST['action']           = 'rocket_beacon';
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
