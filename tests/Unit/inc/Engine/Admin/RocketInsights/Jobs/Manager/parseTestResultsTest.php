<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Jobs\Manager;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as RocketInsightsQuery;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Tests\Unit\TestCase;
use ReflectionMethod;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager::parse_test_results
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class ParseTestResultsTest extends TestCase {

	private $manager;
	private $query_mock;

	protected function setUp(): void {
		parent::setUp();

		// Create a mock for the query dependency
		$this->query_mock = Mockery::mock( RocketInsightsQuery::class );

		// Create manager instance with mocked dependency
		$this->manager = new Manager( $this->query_mock );
	}

	protected function tearDown(): void {
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldParseTestResultsCorrectly( $config, $expected ) {
		// Use reflection to access private method
		$method = new ReflectionMethod( Manager::class, 'parse_test_results' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->manager, $config['api_response'] );

		$this->assertEquals( $expected['report_url'], $result['report_url'] );
		$this->assertEquals( $expected['performance_score'], $result['performance_score'] );

		if ( null === $expected['metric_data'] ) {
			$this->assertNull( $result['metric_data'] );
		} else {
			$this->assertIsArray( $result['metric_data'] );
			$this->assertEquals( $expected['metric_data'], $result['metric_data'] );
		}
	}
}
