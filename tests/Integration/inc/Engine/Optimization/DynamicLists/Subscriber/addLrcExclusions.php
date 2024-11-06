<?php

namespace WP_Rocket\tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::add_lrc_exclusions()
 *
 * @group  DynamicLists
 */
class Test_AddLrcExclusions extends TestCase {
	private $dynamic_lists;
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_lrc_exclusions', 'add_lrc_exclusions', 10 );
	}

	public function tear_down() {
		remove_filter( 'pre_transient_wpr_dynamic_lists', [$this, 'set_dynamic_list'] );

		$this->restoreWpHook( 'rocket_lrc_exclusions' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->dynamic_lists = $config['dynamic_lists'];
		add_filter( 'pre_transient_wpr_dynamic_lists', [$this, 'set_dynamic_list'], 12 );

		$this->assertSame(
			$expected,
			wpm_apply_filters_typed('array', 'rocket_lrc_exclusions', $config['exclusions'] )
		);
	}

	public function set_dynamic_list() {
		return $this->dynamic_lists;
	}
}
