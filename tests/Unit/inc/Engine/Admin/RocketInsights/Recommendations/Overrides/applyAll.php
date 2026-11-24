<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\Overrides;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides\Override_Interface;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides\Overrides;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides\Overrides::apply_all
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_ApplyAll extends TestCase {

	/**
	 * Test that only the overrides matching a recommendation slug are applied, in registration order, and the removed ones are dropped.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected recommendations.
	 */
	public function testShouldReturnExpectedRecommendations( $config, $expected ) {
		$registry = new Overrides();

		foreach ( $config['overrides'] as $slug => $suffix ) {
			$override = Mockery::mock( Override_Interface::class );
			$override->shouldReceive( 'get_option_slug' )->andReturn( $slug );
			$override->shouldReceive( 'should_remove' )->andReturn( in_array( $slug, $config['removed'] ?? [], true ) );
			$override->shouldReceive( 'apply' )->andReturnUsing(
				function ( array $recommendation ) use ( $suffix ) {
					$recommendation['title'] .= $suffix;

					return $recommendation;
				}
			);

			$registry->add( $override );
		}

		$this->assertSame( $expected, $registry->apply_all( $config['recommendations'] ) );
	}
}
