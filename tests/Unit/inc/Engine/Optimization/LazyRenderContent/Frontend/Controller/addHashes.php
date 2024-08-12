<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\LazyRenderContent\Frontend\Controller;

use Mockery;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Controller;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\Processor;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group LRC
 */
class TestAddHashes extends TestCase {
	private $controller;
	private $processor;

	public function set_up() {
		parent::set_up();

		$this->processor  = Mockery::mock( Processor::class );
		$this->controller = new Controller( $this->processor );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		$this->assertSame(
			$expected,
			$this->controller->add_hashes( $html )
		);
	}
}
