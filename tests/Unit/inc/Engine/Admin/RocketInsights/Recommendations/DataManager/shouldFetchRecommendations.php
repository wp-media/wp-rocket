<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\DataManager;

use Brain\Monkey\Functions;
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
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager::should_fetch_recommendations
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_ShouldFetchRecommendations extends TestCase {
	use HasLoggerTrait;

	/**
	 * Options mock.
	 *
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

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
		$this->options      = Mockery::mock( Options_Data::class );
		$this->global_score = Mockery::mock( GlobalScore::class );
		$metric_formatter   = Mockery::mock( MetricFormatter::class );
		$ri_query           = $this->createMock( RocketInsightsQuery::class );

		$this->data_manager = new DataManager( $api_client, $this->options, $this->global_score, $metric_formatter, $ri_query );
		$this->set_logger( $this->data_manager );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		Functions\expect( 'get_transient' )
			->with( 'wpr_ri_recommendations' )
			->andReturn( $config['transient_data'] );

		if ( false !== $config['transient_data'] && ( ! isset( $config['transient_data']['status'] ) || ! isset( $config['transient_data']['timestamp'] ) ) ) {
			Functions\expect( 'delete_transient' )
				->with( 'wpr_ri_recommendations' )
				->once();
		}

		if ( $config['calculate_hash'] ) {
			$this->global_score->shouldReceive( 'get_global_score_data' )
				->andReturn( $config['global_score_data'] );

			$this->options->shouldReceive( 'get' )
				->andReturnUsing(
					function ( $key, $default ) use ( $config ) {
						return $config['options'][ $key ] ?? $default;
					}
				);

			Functions\expect( 'wp_json_encode' )
				->andReturnUsing(
					function ( $data ) {
						return json_encode( $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
					}
				);
		}

		$result = $this->data_manager->should_fetch_recommendations();

		$this->assertSame( $expected, $result );
	}
}
