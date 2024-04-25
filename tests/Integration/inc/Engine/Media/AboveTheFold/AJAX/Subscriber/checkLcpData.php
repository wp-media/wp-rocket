<?php

namespace WP_Rocket\Tests\Integration\Inc\Engine\Media\AboveTheFold\AJAX\Subscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * @covers WP_Rocket\Engine\Media\AboveTheFold\AJAX\Subscriber::check_lcp_data
 *
 * @group AboveTheFold
 */
class Test_CheckLcpData extends AjaxTestCase {
	private $allowed;

	public function set_up() {
		parent::set_up();

		$this->action = 'rocket_check_lcp';
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
		$_POST = $config['post'];

		$this->allowed = $config['filter'];

		add_filter( 'rocket_above_the_fold_optimization', [ $this, 'set_allowed' ] );

		if ( ! empty( $config['row'] ) ) {
			self::addLcp( $config['row'] );
		}

		$result = $this->callAjaxAction();

		$this->assertSame( $expected['result'], $result->success );
	}
	public function set_allowed() {
		return $this->allowed;
	}
}
