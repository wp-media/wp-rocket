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
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager::maybe_fetch_recommendations
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_MaybeFetchRecommendations extends TestCase {
	use HasLoggerTrait;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$global_score = Mockery::mock( GlobalScore::class );

		$data_manager = new DataManager(
			Mockery::mock( APIClient::class ),
			Mockery::mock( Options_Data::class ),
			$global_score,
			Mockery::mock( MetricFormatter::class ),
			$this->createMock( RocketInsightsQuery::class )
		);
		$this->set_logger( $data_manager );

		// The current user's language is always fr.
		Functions\when( 'get_user_locale' )->justReturn( 'fr_FR' );
		Functions\expect( 'get_transient' )
			->with( 'wpr_ri_recommendations' )
			->andReturn( $config['transient_data'] );

		if ( $expected['bails'] ) {
			$global_score->shouldReceive( 'get_global_score_data' )->never();
		} else {
			$global_score->shouldReceive( 'get_global_score_data' )->once()->andReturn( [ 'status' => 'no-url' ] );
			Functions\expect( 'set_transient' )->once();
		}

		$data_manager->maybe_fetch_recommendations();
	}
}
