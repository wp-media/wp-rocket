<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\Render;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Render::get_dashboard_recommendations
 *
 * @group RocketInsights
 * @group Recommendations
 * @runTestsInSeparateProcesses
 */
class Test_GetDashboardRecommendations extends TestCase {
	/**
	 * Stubs the WordPress functions used by the view.
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\stubEscapeFunctions();
		Functions\stubTranslationFunctions();
		Functions\when( 'esc_url' )->returnArg();

		$filesystem = Mockery::mock();
		$filesystem->shouldReceive( 'is_readable' )->andReturn( true );
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $filesystem );
	}

	/**
	 * Builds a Render whose Beacon returns the given help data.
	 *
	 * @param array $help Beacon suggest data.
	 * @return Render
	 */
	private function get_render( array $help ): Render {
		$data_manager = Mockery::mock( DataManager::class );
		$beacon       = Mockery::mock( Beacon::class );

		$data_manager->shouldReceive( 'has_tracked_pages' )->andReturn( true );
		$beacon->shouldReceive( 'get_suggest' )->with( 'rocket_insights' )->once()->andReturn( $help );

		return new Render( dirname( __DIR__, 8 ) . '/views/settings', $data_manager, $beacon );
	}

	/**
	 * Renders the Beacon id and uses the language-aware Beacon article URL as the href.
	 */
	public function testShouldRenderBeaconIdAndBeaconUrlHref() {
		$render = $this->get_render(
			[
				'id'  => '68beeaab7f1dbe419d37a71c',
				'url' => 'https://docs.wp-rocket.me/article/1876-rocket-insights?utm_source=wp_plugin',
			]
			);

		$html = $render->get_dashboard_recommendations( false );

		$this->assertStringContainsString( 'data-beacon-id="68beeaab7f1dbe419d37a71c"', $html );
		$this->assertStringContainsString( 'href="https://docs.wp-rocket.me/article/1876-rocket-insights?utm_source=wp_plugin"', $html );
		$this->assertStringContainsString( 'wpr-infoAction--help', $html );
	}

	/**
	 * Omits the Beacon id attribute and renders an empty href when the Beacon provides no help data.
	 */
	public function testShouldNotRenderBeaconIdWhenEmpty() {
		$render = $this->get_render(
			[
				'id'  => '',
				'url' => '',
			]
			);

		$html = $render->get_dashboard_recommendations( false );

		$this->assertStringNotContainsString( 'data-beacon-id', $html );
		$this->assertStringContainsString( 'href=""', $html );
	}
}
