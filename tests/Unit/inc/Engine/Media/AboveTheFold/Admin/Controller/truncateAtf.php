<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\AboveTheFold\Admin\Controller;

use Mockery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Admin\Controller;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Media\AboveTheFold\Admin\Controller::truncate_atf
 *
 * @group ATF
 */
class Test_TruncateAtf extends TestCase {
	private $controller;
	private $query;
	private $table;
	private $context;

	protected function setUp(): void {
		parent::setUp();

		$this->query = $this->createMock( ATFQuery::class );
		$this->table = $this->createMock( ATFTable::class );
		$this->context = Mockery::mock( Context::class );
		$this->controller = new Controller( $this->table, $this->query, $this->context );
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

		$this->controller->truncate_atf();
	}
}
