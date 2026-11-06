<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\LazyRenderContent\Context\Context;

use Brain\Monkey\{Filters, Functions};
use WP_Rocket\Engine\Optimization\LazyRenderContent\Context\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group LRC
 */
class TestIsAllowed extends TestCase {
	private $context;

	public function set_up() {
		parent::set_up();

		$this->context = new Context();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\when( 'get_option' )->justReturn( $config['licence'] );
		Filters\expectApplied( 'rocket_lrc_optimization' )
			->andReturn( $config['filter'] );

		$this->assertSame(
			$expected,
			$this->context->is_allowed()
		);
	}
}
