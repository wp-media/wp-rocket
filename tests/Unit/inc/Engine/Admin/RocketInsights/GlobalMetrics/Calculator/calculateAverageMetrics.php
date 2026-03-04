<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\GlobalMetrics\Calculator;

use PHPUnit\Framework\MockObject\MockObject;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalMetrics\Calculator;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\GlobalMetrics\Calculator::calculate_average_metrics
 *
 * @group RocketInsights
 * @group GlobalMetrics
 */
class Test_CalculateAverageMetrics extends TestCase {

	/**
	 * Query mock.
	 *
	 * @var MockObject|Query
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
		// Mock get_completed_metrics
		$this->query_mock->expects( $this->once() )
			->method( 'get_completed_metrics' )
			->willReturn( $config['tests'] );

		$result = $this->calculator->calculate_average_metrics();

		$this->assertSame( $expected['lcp'], $result['lcp'] );
		$this->assertSame( $expected['ttfb'], $result['ttfb'] );
		$this->assertSame( $expected['cls'], $result['cls'] );
		$this->assertSame( $expected['tbt'], $result['tbt'] );
	}
}
