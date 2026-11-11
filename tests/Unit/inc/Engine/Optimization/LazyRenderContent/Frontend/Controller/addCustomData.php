<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\LazyRenderContent\Frontend\Controller;

use Mockery;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Context\Context;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Controller;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\Processor;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group LRC
 */
class TestAddCustomData extends TestCase {
	private $controller;
	private $context;

	public function set_up() {
		parent::set_up();

		$this->context = Mockery::mock( Context::class );

		$this->controller = new Controller( Mockery::mock( Processor::class ), $this->context );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $data, $expected ) {
		$this->context->shouldReceive( 'is_allowed' )
			->once()
			->andReturn( $config['is_allowed'] );

		$this->assertSame(
			$expected,
			$this->controller->add_custom_data( $data )
		);
	}
}
