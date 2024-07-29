<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Common\PerformanceHints\Admin\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use function Brain\Monkey\Functions;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\Controller;
use WP_Rocket\Engine\Media\AboveTheFold\Factory as ATFFactory;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold;

/**
 * Test class covering WP_Rocket\Engine\Common\PerformanceHints\Admin\Controller::delete_term
 *
 * @group PerformanceHints
 */
class Test_DeleteTerm extends TestCase {
	private $factories;
	private $queries;

	protected function setUp(): void {
		parent::setUp();

		$this->queries = $this->createMock(AboveTheFold::class);
		$atf_factory = $this->createMock(ATFFactory::class);
		$atf_factory->method('queries')->willReturn($this->queries);

		$this->factories = [
			$atf_factory,
		];
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$controller = new Controller( ! $config['filter'] ? [] : $this->factories );

		Functions\when( 'get_term_link' )->justReturn( $config['url'] );
		Functions\when( 'is_wp_error' )->justReturn( ! $expected );

		if ( $expected ) {
			$this->queries->expects( $this->once() )
				->method( 'delete_by_url' )
				->with( $config['url'] );
		} else {
			$this->queries->expects( $this->never() )
				->method( 'delete_by_url' );
		}

		$controller->delete_term( $config['term_id'] );
	}
}
