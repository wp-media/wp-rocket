<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\GlobalMetrics\Calculator;

use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalMetrics\Calculator;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\GlobalMetrics\Calculator::has_completed_tests
 *
 * @group RocketInsights
 * @group GlobalMetrics
 */
class Test_HasCompletedTests extends TestCase {

	/**
	 * Query mock.
	 *
	 * @var Query
	 */
	private $query_mock;

	/**
	 * Calculator instance.
	 *
	 * @var Calculator
	 */
	private $calculator;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->query_mock = $this->createMock(RocketInsights::class);
		$this->calculator = new Calculator( $this->query_mock );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		// Mock get_completed_count
		$this->query_mock->expects( $this->once() )
			->method( 'get_completed_count' )
			->willReturn( $config['count'] );

		$result = $this->calculator->has_completed_tests();

		$this->assertSame( $expected, $result );
	}
}
