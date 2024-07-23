<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Common\PerformanceHints\Admin\AdminContext;

use Mockery;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\AdminContext;
use WP_Rocket\Engine\Media\AboveTheFold\Admin\Controller;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Media\AboveTheFold\Admin\Controller::truncate_performance_table
 *
 * @group ATF
 */
class Test_TruncatePerformanceTable extends TestCase {
	private $query;
	private $table;
	private $context;

	private $admin_context;
	private $factories;

	protected function setUp(): void {
		parent::setUp();

		$factories = [
			'get_admin_controller'
		];

		$this->factories = $factories;
		$this->query = $this->createMock( ATFQuery::class );
		$this->table = $this->createMock( ATFTable::class );
		$this->context = Mockery::mock( Context::class );
		$this->admin_context = new AdminContext( $this->factories, $this->table, $this->query, $this->context );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->context->shouldReceive( 'is_allowed' )
			->atMost()
			->once()
			->andReturn( $config['filter'] );

		if ( ! $expected ) {
			$this->query->expects( $this->never() )
				->method( 'get_not_completed_count' );
		} else {
			$this->query->expects( $this->once() )
				->method( 'get_not_completed_count' )
				->willReturn( $config['not_completed'] );

			if ( 'partial' === $expected ) {
				$this->table->expects( $this->once() )
					->method( 'remove_all_completed_rows' );
			} elseif ( 'truncate' === $expected ) {
				$this->table->expects( $this->once() )
					->method( 'truncate_atf_table' );
			}
		}

		$this->admin_context->truncate_performance_table();
	}
}
