<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Abilities\Subscriber;

use Brain\Monkey\Functions;
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
 * Tests for WP_Rocket\Engine\Admin\RocketInsights\Abilities\Subscriber::register_rocket_insights_category()
 *
 * @group Admin
 * @group RocketInsights
 * @group Abilities
 */
class Test_RegisterRocketInsightsCategory extends TestCase {
	/**
	 * Test that register_rocket_insights_category() honors the abilities gate.
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

		if ( $expected['wp_register_ability_category_called'] ) {
			Functions\expect( 'wp_register_ability_category' )
				->once()
				->with( 'wp-rocket-insights', Mockery::type( 'array' ) );
		} else {
			Functions\expect( 'wp_register_ability_category' )->never();
		}

		if ( $config['is_enabled'] ) {
			Functions\when( 'wp_register_ability_category' )->justReturn( null );
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

		$subscriber->register_rocket_insights_category();
	}
}
