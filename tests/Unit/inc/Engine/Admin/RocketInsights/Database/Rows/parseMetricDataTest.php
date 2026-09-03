<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Database\Rows;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Rows\RocketInsights;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Database\Rows\RocketInsights::parse_metric_data
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class ParseMetricDataTest extends TestCase {

	protected function tearDown(): void {
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldParseMetricDataCorrectly( $config, $expected ) {
		// Create a minimal stdClass object to pass to constructor
		$item = (object) [
			'id'               => 1,
			'url'              => 'http://example.com',
			'title'            => 'Test',
			'is_mobile'        => 0,
			'status'           => 'completed',
			'job_id'           => 'test-job',
			'queue_name'       => 'test-queue',
			'retries'          => 0,
			'data'             => '{}',
			'submitted_at'     => '2024-01-01 00:00:00',
			'last_accessed'    => '2024-01-01 00:00:00',
			'next_retry_time'  => '2024-01-01 00:00:00',
			'modified'         => '2024-01-01 00:00:00',
			'error_code'       => '',
			'error_message'    => '',
			'score'            => 85,
			'report_url'       => 'http://example.com/report',
			'is_blurred'       => 0,
			'metric_data'      => $config['input'],
		];

		$row = new RocketInsights( $item );

		if ( null === $expected ) {
			$this->assertNull( $row->metric_data );
		} else {
			$this->assertIsArray( $row->metric_data );
			$this->assertEquals( $expected, $row->metric_data );
		}
	}
}
