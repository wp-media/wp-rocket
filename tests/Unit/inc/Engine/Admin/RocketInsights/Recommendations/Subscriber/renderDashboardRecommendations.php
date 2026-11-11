<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\Subscriber;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\{
	DataManager,
	Render,
	Subscriber
};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Subscriber::render_dashboard_recommendations
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_RenderDashboardRecommendations extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$render       = Mockery::mock( Render::class );
		$context      = Mockery::mock( Context::class );
		$data_manager = Mockery::mock( DataManager::class );
		$subscriber   = new Subscriber( $render, $context, $data_manager );

		$context->shouldReceive( 'is_allowed' )->andReturn( $config['is_allowed'] );

		if ( ! $config['is_allowed'] ) {
			$data_manager->shouldReceive( 'get_recommendations' )->never();
			$render->shouldReceive( 'render_dashboard_recommendations' )->never();
		} else {
			$fetched = $config['recommendations'];

			if ( $expected['fetches'] ) {
				$fetched = [
					'status'          => 'completed',
					'recommendations' => [],
				];
				$data_manager->shouldReceive( 'get_recommendations' )->twice()->andReturn( false, $fetched );
				$data_manager->shouldReceive( 'maybe_fetch_recommendations' )->once();
			} else {
				$data_manager->shouldReceive( 'get_recommendations' )->twice()->andReturn( $fetched );
				$data_manager->shouldReceive( 'maybe_fetch_recommendations' )->never();
			}

			$render->shouldReceive( 'render_dashboard_recommendations' )->once()->with( $fetched );
		}

		$subscriber->render_dashboard_recommendations();
	}
}
