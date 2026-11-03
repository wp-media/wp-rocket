<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Abilities\GetRecommendations;

use WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetRecommendations;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetRecommendations::execute
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
		$mock_data_manager = $this->createMock( DataManager::class );

		$mock_data_manager
			->expects( $this->once() )
			->method( 'get_recommendations' )
			->willReturn( $config['recommendations_data'] );

		$get_recommendations = new GetRecommendations( $mock_data_manager );

		$result = $get_recommendations->execute();

		$this->assertSame( $expected['result'], $result );
	}
}
