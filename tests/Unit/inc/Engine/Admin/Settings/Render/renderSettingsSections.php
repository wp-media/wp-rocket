<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Render;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Model\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Renders the real section templates through Render::render_settings_sections().
 *
 * Runs in a separate process: the real templates call WordPress functions when included, and PHPUnit
 * replays the included files of this process into the separate-process tests that run later.
 *
 * @covers \WP_Rocket\Engine\Admin\Settings\Render::render_settings_sections
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @group SettingsPage
 */
class Test_RenderSettingsSections extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
		Functions\when( 'wp_parse_args' )->alias(
			function ( $args, $defaults = [] ) {
				return array_merge( $defaults, (array) $args );
			}
		);
		Functions\when( 'sanitize_html_class' )->returnArg();
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'checked' )->justReturn( '' );
		Functions\when( 'disabled' )->justReturn( '' );

		$filesystem = Mockery::mock();
		$filesystem->shouldReceive( 'is_readable' )->andReturn( true );
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $filesystem );
	}

	/**
	 * Renders one section of the given type with Need Help data.
	 *
	 * @param string $type      Section type.
	 * @param bool   $available Value returned by the assistant availability filter.
	 * @return string
	 */
	private function render_section( string $type, bool $available ): string {
		Filters\expectApplied( 'rocket_ai_assistant_available' )->once()->with( true )->andReturn( $available );

		$render = new Render( dirname( __DIR__, 7 ) . '/views/settings', Mockery::mock( PluginFamily::class ) );

		$render->set_settings(
			[
				'page_slug' => [
					'title'    => 'Page title',
					'sections' => [
						'section_id' => [
							'id'          => 'section_id',
							'type'        => $type,
							'title'       => 'Section title',
							'description' => 'Section description',
							'page'        => 'page_slug',
							'purge_url'   => 'https://example.org/purge',
							// Data read by the CDN section templates.
							'is_active'        => false,
							'is_forced_off'    => false,
							'toggle_tooltip'   => '',
							'cta_data'         => [],
							'status_indicator' => [
								'is_active'           => false,
								'status_text'         => '',
								'no_status_indicator' => true,
								'pages_count'         => 0,
							],
							'help'        => [
								'id'  => 'abc',
								'url' => 'https://docs.example.org/help',
							],
						],
					],
				],
			]
		);

		ob_start();
		$render->render_settings_sections( 'page_slug' );

		return (string) ob_get_clean();
	}

	public function provideNeedHelpTypes() {
		return [
			'fields container'       => [ 'fields_container' ],
			'add-ons container'      => [ 'addons_container' ],
			'no container and title' => [ 'nocontainer_with_title' ],
			'purge section'          => [ 'purge_cdn_cache_section' ],
			'other CDN'              => [ 'your_own_cdn' ],
			'RocketCDN free'         => [ 'rocketcdn_free' ],
			'RocketCDN paid'         => [ 'rocketcdn_paid' ],
		];
	}

	/**
	 * @dataProvider provideNeedHelpTypes
	 */
	public function testShouldRenderNeedHelpWhenTheAssistantIsAvailable( $type ) {
		$html = $this->render_section( $type, true );

		$this->assertStringContainsString( 'data-wap-prompt', $html );
		$this->assertStringContainsString( 'wpr-infoAction--help', $html );
	}

	/**
	 * @dataProvider provideNeedHelpTypes
	 */
	public function testShouldNotRenderNeedHelpWhenTheAssistantIsNotAvailable( $type ) {
		$html = $this->render_section( $type, false );

		$this->assertStringContainsString( 'Section title', $html );
		$this->assertStringNotContainsString( 'data-wap-prompt', $html );
		$this->assertStringNotContainsString( 'wpr-infoAction--help', $html );
		$this->assertStringNotContainsString( 'Need Help?', $html );
	}

	public function testShouldKeepTheLearnMoreLinkOfThePurgeSectionWhenTheAssistantIsNotAvailable() {
		$html = $this->render_section( 'purge_cdn_cache_section', false );

		$this->assertStringContainsString( 'wpr-cdn-purge__learn-more', $html );
		$this->assertStringContainsString( 'Learn more about Purge', $html );
		$this->assertStringContainsString( 'href="https://docs.example.org/help"', $html );
	}

	public function testShouldKeepTheLearnMoreLinkOfThePurgeSectionWhenTheAssistantIsAvailable() {
		$html = $this->render_section( 'purge_cdn_cache_section', true );

		$this->assertStringContainsString( 'Learn more about Purge', $html );
		$this->assertStringContainsString( 'Need Help?', $html );
	}
}
