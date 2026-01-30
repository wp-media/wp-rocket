<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Jobs\Manager;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager::extract_metric_data
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class ExtractMetricDataTest extends TestCase {

	private $manager;

	public function set_up() {
		parent::set_up();

		$query   = Mockery::mock( 'WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights' );
		$context = Mockery::mock( 'WP_Rocket\Engine\Common\Context\ContextInterface' );
		$plan    = Mockery::mock( 'WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan' );

		$this->manager = new Manager( $query, $context, $plan );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldExtractMetricsCorrectly( $config, $expected ) {
		$reflection = new \ReflectionClass( $this->manager );
		$method     = $reflection->getMethod( 'extract_metric_data' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->manager, $config['data'] );

		$this->assertEquals( $expected, $result );
	}
}
