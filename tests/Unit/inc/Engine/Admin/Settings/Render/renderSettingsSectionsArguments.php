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
 * Checks the arguments Render::render_settings_sections() passes to the section renderers, without rendering templates.
 *
 * @covers \WP_Rocket\Engine\Admin\Settings\Render::render_settings_sections
 * @covers \WP_Rocket\Engine\Admin\Settings\Render::hide_need_help
 *
 * @group SettingsPage
 */
class Test_RenderSettingsSectionsArguments extends TestCase {
	private const HELP = [
		'id'  => 'abc',
		'url' => 'https://docs.example.org/help',
	];

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'wp_parse_args' )->alias(
			function ( $args, $defaults = [] ) {
				return array_merge( $defaults, (array) $args );
			}
		);
		Functions\when( 'sanitize_html_class' )->returnArg();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		Filters\expectApplied( 'rocket_ai_assistant_available' )->once()->with( true )->andReturn( $config['available'] );

		$received = [];

		/** @var \Mockery\MockInterface&Render $render The section templates are not part of this test. */
		$render = Mockery::mock( Render::class, [ 'vfs://views', Mockery::mock( PluginFamily::class ) ] )->makePartial();
		$render->shouldReceive( $config['type'] )->once()->andReturnUsing(
			function ( $args ) use ( &$received ) {
				$received = $args;
			}
		);

		$render->set_settings(
			[
				'page_slug' => [
					'title'    => 'Page title',
					'sections' => [
						[
							'id'    => 'section_id',
							'type'  => $config['type'],
							'title' => 'Section title',
							'class' => [ 'one', 'two' ],
							'help'  => self::HELP,
						],
					],
				],
			]
		);

		$render->render_settings_sections( 'page_slug' );

		$this->assertSame( $expected['help'], $received['help'] );

		if ( $expected['has_hide_need_help'] ) {
			$this->assertTrue( $received['hide_need_help'] );
		} else {
			$this->assertArrayNotHasKey( 'hide_need_help', $received );
		}

		if ( isset( $expected['class'] ) ) {
			$this->assertSame( $expected['class'], $received['class'] );
		}
	}
}
