<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\DataManager;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as RocketInsightsQuery;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\MetricFormatter;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\APIClient;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager::has_tracked_pages
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_HasTrackedPages extends TestCase {
	use HasLoggerTrait;

	/**
	 * GlobalScore mock.
	 *
	 * @var GlobalScore|Mockery\MockInterface
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

		$api_client       = Mockery::mock( APIClient::class );
		$options          = Mockery::mock( Options_Data::class );
		$metric_formatter = Mockery::mock( MetricFormatter::class );
		$ri_query         = $this->createMock( RocketInsightsQuery::class );

		$this->global_score = Mockery::mock( GlobalScore::class );

		$this->data_manager = new DataManager( $api_client, $options, $this->global_score, $metric_formatter, $ri_query );
		$this->set_logger( $this->data_manager );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->global_score
			->shouldReceive( 'get_global_score_data' )
			->once()
			->andReturn( $config['global_score_data'] );

		$this->assertSame( $expected, $this->data_manager->has_tracked_pages() );
	}
}
