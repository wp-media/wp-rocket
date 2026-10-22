<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\GlobalMetrics\Calculator;

use WP_Rocket\Engine\Admin\RocketInsights\GlobalMetrics\Calculator;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\GlobalMetrics\Calculator::get_metric_definitions
 *
 * @group RocketInsights
 * @group GlobalMetrics
 */
class Test_GetMetricDefinitions extends TestCase {

	/**
	 * Test that the metric definitions are returned in display order.
	 *
	 * @param array $expected Expected metric definitions.
	 *
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $expected ) {
		$this->stubTranslationFunctions();

		$result = Calculator::get_metric_definitions();

		$this->assertSame( $expected, $result );
		$this->assertSame( Calculator::METRIC_KEYS, array_keys( $result ) );
	}
}
