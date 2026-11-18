<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Controller;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Engine\Admin\RocketInsights\Queue\Queue;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Rest as RecommendationsRest;
use WP_Rocket\Engine\Admin\RocketInsights\Render;
use WP_Rocket\Engine\Admin\RocketInsights\Rest;
use WP_Rocket\Engine\Admin\RocketInsights\Subscriber;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Subscriber::render_dashboard_global_score
 *
 * @group RocketInsights
 */
class Test_RenderDashboardGlobalScore extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$render     = Mockery::mock( Render::class );
		$controller = Mockery::mock( Controller::class );
		$context    = Mockery::mock( Context::class );

		$subscriber = new Subscriber(
			$render,
			$controller,
			Mockery::mock( Rest::class ),
			Mockery::mock( Queue::class ),
			$context,
			Mockery::mock( GlobalScore::class ),
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Manager::class ),
			Mockery::mock( Plan::class ),
			Mockery::mock( Renewal::class ),
			Mockery::mock( RecommendationsRest::class )
		);

		$context->shouldReceive( 'is_allowed' )->andReturn( $config['is_allowed'] );

		if ( null === $expected['data'] ) {
			$controller->shouldReceive( 'get_global_score' )->never();
			$render->shouldReceive( 'render_dashboard_global_score' )->never();
			$context->shouldReceive( 'is_reseller_or_non_live' )->once()->andReturn( $config['is_local'] );
			$render->shouldReceive( 'render_dashboard_disabled_state' )->once()->with( $expected['disabled_local'] );
		} else {
			$controller->shouldReceive( 'get_global_score' )->once()->andReturn( $config['score'] );
			$controller->shouldReceive( 'get_remaining_url_count' )->once()->andReturn( $config['remaining_urls'] );
			$render->shouldReceive( 'render_dashboard_global_score' )->once()->with( $expected['data'] );
		}

		$subscriber->render_dashboard_global_score();
	}
}
