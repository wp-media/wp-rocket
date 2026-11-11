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
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Subscriber::output_recommendations_rest_response
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_OutputRecommendationsRestResponse extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$render       = Mockery::mock( Render::class );
		$data_manager = Mockery::mock( DataManager::class );
		$subscriber   = new Subscriber( $render, Mockery::mock( Context::class ), $data_manager );

		$fetched = [
			'status'          => 'completed',
			'recommendations' => [],
		];

		if ( $expected['fetches'] ) {
			$data_manager->shouldReceive( 'get_recommendations' )->twice()->andReturn( false, $fetched );
			$data_manager->shouldReceive( 'maybe_fetch_recommendations' )->once();
		} else {
			$data_manager->shouldReceive( 'get_recommendations' )->twice()->andReturn( $fetched );
			$data_manager->shouldReceive( 'maybe_fetch_recommendations' )->never();
		}

		$render->shouldReceive( 'render_recommendations_widget' )->once()->with( $fetched, false )->andReturn( '<widget>' );
		$render->shouldReceive( 'get_dashboard_recommendations' )->once()->with( $fetched )->andReturn( '<dashboard>' );

		$this->assertSame(
			[
				'html'           => '<widget>',
				'dashboard_html' => '<dashboard>',
			],
			$subscriber->output_recommendations_rest_response( [] )['recommendations']
		);
	}
}
