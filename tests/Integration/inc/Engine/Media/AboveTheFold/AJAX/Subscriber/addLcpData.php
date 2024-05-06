<?php

namespace WP_Rocket\Tests\Integration\Inc\Engine\Media\AboveTheFold\AJAX\Subscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * Test class covering WP_Rocket\Engine\Media\AboveTheFold\AJAX\Subscriber::add_lcp_data
 *
 * @group AboveTheFold
 */
class Test_AddLcpData extends AjaxTestCase {
	private $allowed;

	public function set_up() {
		parent::set_up();

		$this->action = 'rocket_lcp';

		$this->clean_table();
	}

	/**
	 * $_POST is cleared in parent method
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_filter( 'rocket_above_the_fold_optimization', [ $this, 'set_allowed' ] );
		$this->clean_table();

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

	private function clean_table() {
		$container = apply_filters( 'rocket_container', null );
		$atf_table = $container->get( 'atf_table' );

		$result = $atf_table->truncate();

		$this->assertTrue( $result );
	}
}
