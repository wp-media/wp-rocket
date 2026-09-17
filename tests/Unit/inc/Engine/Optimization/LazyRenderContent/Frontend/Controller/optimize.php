<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\LazyRenderContent\Frontend\Controller;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Context\Context;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Rows\LazyRenderContent as LRCRow;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Controller;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\Processor;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group LRC
 */
class TestOptimize extends TestCase {
	private $controller;

	public function set_up() {
		parent::set_up();

		$this->controller = new Controller( Mockery::mock( Processor::class ), Mockery::mock( Context::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		// Pre-existing gap: optimize() calls rocket_bypass() unconditionally (before checking
		// has_lrc()), but this was never stubbed here, so every case in this class errored with
		// "Call to undefined function ... rocket_bypass()" prior to this fix. Stubbing it to
		// false preserves this class's original intent of testing has_lrc()-driven behavior only.
		Functions\expect( 'rocket_bypass' )
			->atMost()
			->once()
			->andReturn( false );

		Filters\expectApplied( 'rocket_disable_meta_generator' )
			->atMost()
			->once()
			->andReturn( true );

		$row = $this->getMockBuilder( LRCRow::class )
					->disableOriginalConstructor()
					->getMock();

		$row->expects( $this->once() )
			->method( 'has_lrc' )
			->willReturn( $config['has_lrc'] );

		$row->below_the_fold = $config['below_the_fold'];

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $this->controller->optimize( $html, $row ) )
		);
	}
}
