<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Abilities\Subscriber;

use Mockery;
use WP_Rocket\Engine\Abilities\Context as AbilitiesContext;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\AddPageInsights;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetInsightsScore;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetPageInsightsScore;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetRecommendations;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\RemovePageInsights;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\RetestPageInsights;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Admin\RocketInsights\Abilities\Subscriber::register_get_insights_scores_ability()
 *
 * @group Admin
 * @group RocketInsights
 * @group Abilities
 */
class Test_RegisterGetInsightsScoresAbility extends TestCase {
	/**
	 * Test that register_get_insights_scores_ability() honors the abilities gate.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, array $expected ): void {
		$get_insights_score      = Mockery::mock( GetInsightsScore::class );
		$add_page_insights       = Mockery::mock( AddPageInsights::class );
		$retest_page_insights    = Mockery::mock( RetestPageInsights::class );
		$remove_page_insights    = Mockery::mock( RemovePageInsights::class );
		$get_recommendations     = Mockery::mock( GetRecommendations::class );
		$get_page_insights_score = Mockery::mock( GetPageInsightsScore::class );
		$abilities_context       = Mockery::mock( AbilitiesContext::class );

		$abilities_context->shouldReceive( 'is_enabled' )
			->once()
			->andReturn( $config['is_enabled'] );

		if ( $expected['register_called'] ) {
			$get_insights_score->shouldReceive( 'register' )->once();
		} else {
			$get_insights_score->shouldReceive( 'register' )->never();
		}

		$subscriber = new Subscriber(
			$get_insights_score,
			$add_page_insights,
			$retest_page_insights,
			$remove_page_insights,
			$get_recommendations,
			$get_page_insights_score,
			$abilities_context
		);

		$subscriber->register_get_insights_scores_ability();
	}
}
