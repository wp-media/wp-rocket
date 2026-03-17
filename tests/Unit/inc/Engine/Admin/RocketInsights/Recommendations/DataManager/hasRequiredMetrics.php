<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\DataManager;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\MetricFormatter;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\APIClient;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager::has_required_metrics
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_HasRequiredMetrics extends TestCase {
	use HasLoggerTrait;

	/**
	 * Global Score mock.
	 *
	 * @var Mockery\MockInterface|GlobalScore
	 */
	private $global_score;

	/**
	 * DataManager instance.
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();

		$api_client         = Mockery::mock( APIClient::class );
		$options            = Mockery::mock( Options_Data::class );
		$this->global_score = Mockery::mock( GlobalScore::class );
		$metric_formatter = Mockery::mock( MetricFormatter::class );

		$this->data_manager = new DataManager( $api_client, $options, $this->global_score, $metric_formatter );
		$this->set_logger( $this->data_manager );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->global_score->shouldReceive( 'get_global_score_data' )
			->andReturn( $config['global_score_data'] );

		$result = $this->data_manager->has_required_metrics();

		$this->assertSame( $expected, $result );
	}
}
