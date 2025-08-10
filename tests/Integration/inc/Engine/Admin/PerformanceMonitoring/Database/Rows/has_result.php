<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Database\Rows;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Rows\PerformanceMonitoring as PerformanceMonitoringRow;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Rows\PerformanceMonitoring::has_result
 *
 * @group PerformanceMonitoring
 */
class Test_HasResult extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldWorkAsExpected( $config, $expected ) {
		$row = new PerformanceMonitoringRow( (object) $config );

		$result = $row->has_result();

		$this->assertEquals( $expected, $result );
	}
}
