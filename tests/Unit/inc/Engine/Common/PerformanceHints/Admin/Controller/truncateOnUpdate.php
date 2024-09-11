<?php
declare(strict_types=1);

namespace WP_Rocket\tests\Unit\inc\Engine\Common\PerformanceHints\Admin\Controller;

use Mockery;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\Controller;
use WP_Rocket\Engine\Media\AboveTheFold\Factory as ATFFactory;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;

/**
 * Test class covering WP_Rocket\Engine\Common\PerformanceHints\Admin\Controller::truncate_on_update
 *
 * @group PerformanceHints
 */
class Test_TruncateOnUpdate extends TestCase {
	private $factories;
	private $queries;
	private $table;
	private $context;

	protected function setUp(): void {
		parent::setUp();

		$this->queries = $this->createMock(AboveTheFold::class);
		$this->table = $this->createMock(ATFTable::class);
		$atf_factory = $this->createMock(ATFFactory::class);
		$this->context = $this->createMock(ContextInterface::class);
		$atf_factory->method('queries')->willReturn($this->queries);
		$atf_factory->method('table')->willReturn($this->table);
		$atf_factory->method('get_context')->willReturn($this->context);

		$this->factories = [
			$atf_factory,
		];
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$controller = new Controller( ! $config['filter'] ? [] : $this->factories );

		if ( ! $expected ) {
			$this->queries->expects( $this->never() )
				->method( 'get_not_completed_count' );
		} else {
			$this->context->expects( $this->once() )
				->method('is_allowed')
				->willReturn(true);

			$this->queries->expects( $this->once() )
				->method( 'get_not_completed_count' )
				->willReturn( $config['not_completed'] );

			$this->table->expects( $this->once() )
				->method( 'truncate_table' );
		}

		$controller->truncate_on_update( $config['new_version'], $config['old_version'] );
	}
}
