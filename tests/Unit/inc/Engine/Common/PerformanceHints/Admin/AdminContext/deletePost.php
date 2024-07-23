<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Common\PerformanceHints\Admin\AdminContext;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\AdminContext;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Tests\Unit\TestCase;
use function Brain\Monkey\Functions;

/**
 * Test class covering WP_Rocket\Engine\Common\PerformanceHints\Admin\AdminContext::delete_post
 *
 * @group ATF
 */
class Test_DeletePost extends TestCase {
	private $query;
	private $table;
	private $context;
	private $factories;
	private $admin_context;

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

		Functions\when( 'get_permalink' )->justReturn( $config['url'] );

		if ( $expected ) {
			$this->query->expects( $this->once() )
				->method( 'delete_by_url' )
				->with( $config['url'] );
		} else {
			$this->query->expects( $this->never() )
				->method( 'delete_by_url' );
		}

		$this->admin_context->delete_post( $config['post_id'] );
	}
}
