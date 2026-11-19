<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\GlobalMetrics\Subscriber;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalMetrics\Calculator;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalMetrics\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\GlobalMetrics\Subscriber::add_average_metrics
 *
 * @group RocketInsights
 * @group GlobalMetrics
 */
class Test_AddAverageMetrics extends TestCase {

	/**
	 * Calculator mock.
	 *
	 * @var Mockery\MockInterface|Calculator
	 */
	private $calculator_mock;

	/**
	 * Subscriber instance.
	 *
	 * @var Subscriber
	 */
	private $subscriber;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->calculator_mock = Mockery::mock( Calculator::class );
		$this->subscriber      = new Subscriber( $this->calculator_mock );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		// Mock calculate_average_metrics (only if tests exist)
		if ( 'in-progress' !== $config['status'] ) {
			$this->calculator_mock->shouldReceive( 'calculate_average_metrics' )
				->once()
				->andReturn( $config['metrics'] );
		} else {
			$this->calculator_mock->shouldNotReceive( 'calculate_average_metrics' );
		}

		$result = $this->subscriber->add_average_metrics( $config['input_data'] );

		$this->assertSame( $expected, $result );
	}
}
