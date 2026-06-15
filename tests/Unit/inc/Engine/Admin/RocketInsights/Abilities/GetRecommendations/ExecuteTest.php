<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Abilities\GetRecommendations;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetRecommendations;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetRecommendations::execute
 * and \WP_Rocket\Engine\Admin\RocketInsights\Abilities\GetRecommendations::check_permissions
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

	public function testCheckPermissionsReturnsTrueWhenUserHasCapability(): void {
		Functions\when( 'current_user_can' )
			->justReturn( true );

		$mock_data_manager   = $this->createMock( DataManager::class );
		$get_recommendations = new GetRecommendations( $mock_data_manager );

		$this->assertTrue( $get_recommendations->check_permissions() );
	}

	public function testCheckPermissionsReturnsFalseWhenUserLacksCapability(): void {
		Functions\when( 'current_user_can' )
			->justReturn( false );

		$mock_data_manager   = $this->createMock( DataManager::class );
		$get_recommendations = new GetRecommendations( $mock_data_manager );

		$this->assertFalse( $get_recommendations->check_permissions() );
	}
}
