<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\LazyRenderContent\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Subscriber::add_hashes_when_allowed
 *
 * @group PerformanceHints
 */
class Test_AddHashesWhenAllowed extends TestCase {
	private $filter;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'add_hashes_when_allowed', 16 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_buffer' );
		remove_filter( 'rocket_lrc_optimization', '__return_false' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->filter = $config['filter'];

		add_filter( 'rocket_lrc_optimization', [ $this, 'returnFilter' ] );

		self::addLrc( $config['row'] );

		add_filter( 'rocket_lrc_optimization', '__return_true' );

		$this->assertSame(
			$expected['html'],
			apply_filters( 'rocket_buffer', $config['html'] )
		);
	}

	public function returnFilter() {
		return $this->filter;
	}
}
