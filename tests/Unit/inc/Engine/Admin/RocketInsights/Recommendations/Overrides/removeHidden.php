<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\Overrides;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides\Override_Interface;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides\Overrides;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides\Overrides::remove_hidden
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_RemoveHidden extends TestCase {

	/**
	 * Test that only the recommendations an override removes are dropped, and apply() is never called.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected recommendations.
	 */
	public function testShouldReturnExpectedRecommendations( $config, $expected ) {
		$registry = new Overrides();

		foreach ( $config['overrides'] as $slug ) {
			$override = Mockery::mock( Override_Interface::class );
			$override->shouldReceive( 'get_option_slug' )->andReturn( $slug );
			$override->shouldReceive( 'should_remove' )->andReturn( in_array( $slug, $config['removed'], true ) );
			$override->shouldNotReceive( 'apply' );

			$registry->add( $override );
		}

		$this->assertSame( $expected, $registry->remove_hidden( $config['recommendations'] ) );
	}
}
