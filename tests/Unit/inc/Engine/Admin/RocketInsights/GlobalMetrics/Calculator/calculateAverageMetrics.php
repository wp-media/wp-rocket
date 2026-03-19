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
		$this->stubTranslationFunctions();

		// Mock get_completed_metrics
		$this->query_mock->expects( $this->once() )
			->method( 'get_completed_metrics' )
			->willReturn( $config['tests'] );

		$result = $this->calculator->calculate_average_metrics();

		// Check largest_contentful_paint (LCP)
		if ( null === $expected['largest_contentful_paint'] ) {
			$this->assertNull( $result['largest_contentful_paint'] );
		} else {
			$this->assertSame( $expected['largest_contentful_paint'], $result['largest_contentful_paint']['value'] );
		}

		// Check time_to_first_byte (TTFB)
		if ( null === $expected['time_to_first_byte'] ) {
			$this->assertNull( $result['time_to_first_byte'] );
		} else {
			$this->assertSame( $expected['time_to_first_byte'], $result['time_to_first_byte']['value'] );
		}

		// Check cumulative_layout_shift (CLS)
		if ( null === $expected['cumulative_layout_shift'] ) {
			$this->assertNull( $result['cumulative_layout_shift'] );
		} else {
			$this->assertSame( $expected['cumulative_layout_shift'], $result['cumulative_layout_shift']['value'] );
		}

		// Check total_blocking_time (TBT)
		if ( null === $expected['total_blocking_time'] ) {
			$this->assertNull( $result['total_blocking_time'] );
		} else {
			$this->assertSame( $expected['total_blocking_time'], $result['total_blocking_time']['value'] );
		}
	}
}
