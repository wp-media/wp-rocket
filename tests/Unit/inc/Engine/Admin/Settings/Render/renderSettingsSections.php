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
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		Filters\expectApplied( 'rocket_ai_assistant_available' )->once()->with( true )->andReturn( $config['available'] );

		$render = new Render( dirname( __DIR__, 7 ) . '/views/settings', Mockery::mock( PluginFamily::class ) );

		$render->set_settings(
			[
				'page_slug' => [
					'title'    => 'Page title',
					'sections' => [
						'section_id' => [
							'id'               => 'section_id',
							'type'             => $config['type'],
							'title'            => 'Section title',
							'description'      => 'Section description',
							'page'             => 'page_slug',
							'purge_url'        => 'https://example.org/purge',
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
							'help'             => [
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
		$html = (string) ob_get_clean();

		foreach ( $expected['contains'] as $string ) {
			$this->assertStringContainsString( $string, $html );
		}

		foreach ( $expected['not_contains'] as $string ) {
			$this->assertStringNotContainsString( $string, $html );
		}
	}
}
