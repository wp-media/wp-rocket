<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\AboveTheFold\Admin\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Admin\Controller;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Engine\Media\AboveTheFold\Admin\Controller::delete_term_atf
 *
 * @group ATF
 */
class Test_DeleteTermAtf extends TestCase {
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

		$this->controller->delete_term_atf( $config['term_id'] );
	}
}
