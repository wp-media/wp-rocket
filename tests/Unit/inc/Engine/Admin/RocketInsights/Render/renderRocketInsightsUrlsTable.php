<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Render;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\MetricFormatter;
use WP_Rocket\Engine\Admin\RocketInsights\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Renders the real urls-table template through Render::render_rocket_insights_urls_table().
 *
 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Render::render_rocket_insights_urls_table
 *
 * @group RocketInsights
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Test_RenderRocketInsightsUrlsTable extends TestCase {
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
	 * Renders the table with the Beacon returning help data.
	 *
	 * @param bool $available Value returned by the assistant availability filter.
	 * @return string
	 */
	private function render_table( bool $available ): string {
		Filters\expectApplied( 'rocket_ai_assistant_available' )->once()->with( true )->andReturn( $available );

		$context = Mockery::mock( Context::class );
		$beacon  = Mockery::mock( Beacon::class );

		$context->shouldReceive( 'is_adding_page_allowed' )->andReturn( true );
		$beacon->shouldReceive( 'get_suggest' )->with( 'rocket_insights' )->andReturn(
			[
				'id'  => 'abc',
				'url' => 'https://docs.example.org/help',
			]
		);

		/** @var \Mockery\MockInterface&Render $render The add button template is not part of this test. */
		$render = Mockery::mock(
			Render::class,
			[ dirname( __DIR__, 7 ) . '/views/settings', $context, $beacon, $this->createMock( Query::class ), $this->createMock( MetricFormatter::class ) ]
		)->makePartial();
		$render->shouldReceive( 'render_add_page_btn' )->andReturnNull();

		ob_start();
		$render->render_rocket_insights_urls_table(
			[
				'items'             => [],
				'upgrade_url'       => 'https://example.org/upgrade',
				'show_quota_banner' => false,
			]
		);

		return (string) ob_get_clean();
	}

	public function testShouldRenderNeedHelpWhenTheAssistantIsAvailable() {
		$html = $this->render_table( true );

		$this->assertStringContainsString( 'data-wap-prompt', $html );
		$this->assertStringContainsString( 'wpr-infoAction--help', $html );
	}

	public function testShouldNotRenderNeedHelpWhenTheAssistantIsNotAvailable() {
		$html = $this->render_table( false );

		$this->assertStringContainsString( 'Performance Summary', $html );
		$this->assertStringNotContainsString( 'data-wap-prompt', $html );
		$this->assertStringNotContainsString( 'wpr-infoAction--help', $html );
	}
}
