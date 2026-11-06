<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\PerformanceHints\Admin\Controller;

use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\Controller;
use WP_Rocket\Engine\Media\AboveTheFold\Factory as ATFFactory;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\PerformanceHints\Admin\Controller::clean_on_logo_change
 *
 * @group PerformanceHints
 */
class CleanOnLogoChangeTest extends TestCase {
	private $queries;
	private $table;
	private $factories;
	private $context;

	protected function setUp(): void {
		parent::setUp();

		$this->queries = $this->createMock( AboveTheFold::class );
		$this->table   = $this->createMock( ATFTable::class );

		$atf_factory = $this->createMock( ATFFactory::class );
		$atf_factory->method( 'queries' )->willReturn( $this->queries );
		$atf_factory->method( 'table' )->willReturn( $this->table );
		$this->context = $this->createMock(ContextInterface::class);
		$atf_factory->method('get_context')->willReturn($this->context);

		$this->factories = [ $atf_factory ];
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$controller = new Controller( ! $config['filter'] ? [] : $this->factories );

		if ( ! empty( $config['filter'] ) ) {
			$this->context->expects( $this->once() )
				->method('is_allowed')
				->willReturn(true);
		}

		if ( $expected['truncate'] ) {
			$this->queries->expects( $this->once() )
				->method( 'get_not_completed_count' )
				->willReturn( 0 );

			$this->table->expects( $this->once() )
				->method( 'truncate' );
		} else {
			$this->queries->expects( $this->never() )
				->method( 'get_not_completed_count' );

			$this->table->expects( $this->never() )
				->method( 'truncate' );
		}

		$result = $controller->clean_on_logo_change( $config['value'], $config['old_value'] );

		$this->assertSame( $expected['return'], $result );
	}
}
