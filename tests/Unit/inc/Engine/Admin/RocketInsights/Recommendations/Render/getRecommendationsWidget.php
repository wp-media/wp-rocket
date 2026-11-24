<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\Render;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides\Overrides;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Render::get_recommendations_widget
 *
 * @group RocketInsights
 * @group Recommendations
 * @runTestsInSeparateProcesses
 */
class Test_GetRecommendationsWidget extends TestCase {
	/**
	 * Stubs the WordPress functions used by the view.
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\stubEscapeFunctions();
		Functions\stubTranslationFunctions();
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'wp_parse_args' )->alias(
			function ( $args, $defaults = [] ) {
				return array_merge( $defaults, (array) $args );
			}
		);

		$filesystem = Mockery::mock();
		$filesystem->shouldReceive( 'is_readable' )->andReturn( true );
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $filesystem );
	}

	/**
	 * Test that the load more button depends on the recommendations left after the overrides.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected values.
	 */
	public function testShouldComputeLoadMoreAfterOverrides( $config, $expected ) {
		$data_manager = Mockery::mock( DataManager::class );
		$overrides    = Mockery::mock( Overrides::class );
		$beacon       = Mockery::mock( Beacon::class );

		$data_manager->shouldReceive( 'has_tracked_pages' )->andReturn( true );
		$data_manager->shouldReceive( 'get_section_from_option_slug' )->andReturn( 'section' );
		$overrides->shouldReceive( 'apply_all' )->andReturnUsing(
			function ( array $recommendations ) use ( $config ) {
				return array_slice( $recommendations, 0, count( $recommendations ) - $config['removed'] );
			}
		);

		$render = new Render( dirname( __DIR__, 8 ) . '/views/settings', $data_manager, $overrides, $beacon );

		$recommendations = [];
		for ( $i = 0; $i < $config['raw_count']; $i++ ) {
			$recommendations[] = [
				'option_slug' => 'option_' . $i,
				'title'       => 'Title ' . $i,
			];
		}

		$html = $render->get_recommendations_widget(
			[
				'status'          => 'completed',
				'recommendations' => $recommendations,
			]
		);

		$this->assertSame( $expected['load_more'], str_contains( $html, 'wpr-recommendations__load-more"' ) );
	}
}
