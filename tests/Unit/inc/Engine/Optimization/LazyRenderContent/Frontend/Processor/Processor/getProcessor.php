<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\LazyRenderContent\Frontend\Processor\Processor;

use Mockery;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\Processor;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\ProcessorInterface;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group LRC
 */
class TestGetProcessor extends TestCase {
	private $processor;

	public function set_up() {
		parent::set_up();

		$this->processor = new Processor();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $processor ) {
		$this->processor->set_processor( $processor );

		$this->assertInstanceOf(
			ProcessorInterface::class,
			$this->processor->get_processor()
		);
	}
}
