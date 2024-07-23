<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Common\PerformanceHints\Admin\AdminContext;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\AboveTheFold\Admin\Controller;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\AdminContext;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Tests\Unit\TestCase;
use function Brain\Monkey\Functions;

/**
 * Test class covering WP_Rocket\Engine\Media\AboveTheFold\Admin\Controller::delete_term
 *
 * @group ATF
 */
class Test_DeleteTerm extends TestCase {
	private $controller;
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

		Functions\when( 'get_term_link' )->justReturn( $config['url'] );
		Functions\when( 'is_wp_error' )->justReturn( ! $expected );

		if ( $expected ) {
			$this->query->expects( $this->once() )
				->method( 'delete_by_url' )
				->with( $config['url'] );
		} else {
			$this->query->expects( $this->never() )
				->method( 'delete_by_url' );
		}

		$this->admin_context->delete_term( $config['term_id'] );
	}
}
