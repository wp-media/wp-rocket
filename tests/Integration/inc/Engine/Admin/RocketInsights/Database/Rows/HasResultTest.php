<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Database\Rows;

use WP_Rocket\Engine\Admin\RocketInsights\Database\Rows\RocketInsights as RocketInsightsRow;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Database\Rows\RocketInsights::has_result
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class HasResultTest extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldWorkAsExpected( $config, $expected ) {
		$row = new RocketInsightsRow( (object) $config );

		$result = $row->has_result();

		$this->assertEquals( $expected, $result );
	}
}
