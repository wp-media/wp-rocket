<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\Render;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides\Overrides;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering the assistant availability in \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Render::get_dashboard_recommendations
 *
 * @group RocketInsights
 * @group Recommendations
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Test_GetDashboardRecommendationsAssistant extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		Functions\stubEscapeFunctions();
		Functions\stubTranslationFunctions();
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'wp_strip_all_tags' )->alias(
			function ( $text ) {
				return trim( strip_tags( $text ) );
			}
		);

		$filesystem = Mockery::mock();
		$filesystem->shouldReceive( 'is_readable' )->andReturn( true );
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $filesystem );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		// The assistant filter does not decide whether "Need Help?" is printed.
		Filters\expectApplied( 'rocket_ai_assistant_available' )->never();

		$data_manager = Mockery::mock( DataManager::class );
		$beacon       = Mockery::mock( Beacon::class );

		$data_manager->shouldReceive( 'has_tracked_pages' )->andReturn( true );
		$beacon->shouldReceive( 'get_suggest' )->with( 'rocket_insights' )->andReturn(
			[
				'id'  => 'abc',
				'url' => 'https://docs.example.org/help',
			]
		);

		$render = new Render( dirname( __DIR__, 8 ) . '/views/settings', $data_manager, new Overrides(), $beacon );

		$html = $render->get_dashboard_recommendations( false );

		foreach ( $expected['contains'] as $string ) {
			$this->assertStringContainsString( $string, $html );
		}

		foreach ( $expected['not_contains'] as $string ) {
			$this->assertStringNotContainsString( $string, $html );
		}
	}
}
