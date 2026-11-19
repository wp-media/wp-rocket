<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Abilities\GetInsightsScore;

use WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetInsightsScore;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetInsightsScore::execute
 *
 * @group Admin
 * @group RocketInsights
 * @group Abilities
 */
class ExecuteTest extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		$mock_query        = $this->createMock( Query::class );
		$mock_global_score = $this->createMock( GlobalScore::class );

		$mock_global_score
			->expects( $this->once() )
			->method( 'get_global_score_data' )
			->willReturn( $config['global_score_data'] );

		if ( $expected['query_called'] ) {
			$mock_query
				->expects( $this->once() )
				->method( 'query' )
				->with( $expected['query_args'] )
				->willReturn( $config['query_results'] );
		} else {
			$mock_query
				->expects( $this->never() )
				->method( 'query' );
		}

		$get_insights_score = new GetInsightsScore( $mock_query, $mock_global_score );

		$result = $get_insights_score->execute( $config['input'] );

		$this->assertSame( $expected['result'], $result );
	}
}
