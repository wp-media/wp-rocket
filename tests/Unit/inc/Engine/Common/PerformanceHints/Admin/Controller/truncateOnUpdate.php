<?php
declare(strict_types=1);

namespace WP_Rocket\tests\Unit\inc\Engine\Common\PerformanceHints\Admin\Controller;

use Mockery;
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
class TestTruncateOnUpdate extends TestCase {
	private $factories;
	private $queries;
	private $table;

	protected function setUp(): void {
		parent::setUp();

		$this->queries = $this->createMock(AboveTheFold::class);
		$this->table = $this->createMock(ATFTable::class);
		$atf_factory = $this->createMock(ATFFactory::class);
		$atf_factory->method('queries')->willReturn($this->queries);
		$atf_factory->method('table')->willReturn($this->table);

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
			$this->queries->expects( $this->once() )
				->method( 'get_not_completed_count' )
				->willReturn( $config['not_completed'] );

			$this->table->expects( $this->once() )
				->method( 'truncate_table' );
		}

		$controller->truncate_on_update( $config['new_version'], $config['old_version'] );
	}
}
