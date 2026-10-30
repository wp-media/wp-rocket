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
	 * Renders one section and returns the arguments the section renderer received.
	 *
	 * @param string $type      Section type.
	 * @param bool   $available Value returned by the assistant availability filter.
	 * @return array
	 */
	private function get_section_arguments( string $type, bool $available ): array {
		Filters\expectApplied( 'rocket_ai_assistant_available' )->once()->with( true )->andReturn( $available );

		$received = [];

		/** @var \Mockery\MockInterface&Render $render The section templates are not part of this test. */
		$render = Mockery::mock( Render::class, [ 'vfs://views', Mockery::mock( PluginFamily::class ) ] )->makePartial();
		$render->shouldReceive( $type )->once()->andReturnUsing(
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
							'type'  => $type,
							'title' => 'Section title',
							'class' => [ 'one', 'two' ],
							'help'  => self::HELP,
						],
					],
				],
			]
		);

		$render->render_settings_sections( 'page_slug' );

		return $received;
	}

	public function testShouldPassTheHelpDataWhenTheAssistantIsAvailable() {
		$args = $this->get_section_arguments( 'fields_container', true );

		$this->assertSame( self::HELP, $args['help'] );
		$this->assertArrayNotHasKey( 'hide_need_help', $args );
		$this->assertSame( 'one two', $args['class'] );
	}

	public function testShouldBlankTheHelpDataWhenTheAssistantIsNotAvailable() {
		$args = $this->get_section_arguments( 'fields_container', false );

		$this->assertSame( '', $args['help'] );
		$this->assertArrayNotHasKey( 'hide_need_help', $args );
	}

	public function testShouldKeepTheHelpDataOfThePurgeSectionAndFlagIt() {
		$args = $this->get_section_arguments( 'purge_cdn_cache_section', false );

		$this->assertSame( self::HELP, $args['help'] );
		$this->assertTrue( $args['hide_need_help'] );
	}

	public function testShouldNotFlagThePurgeSectionWhenTheAssistantIsAvailable() {
		$args = $this->get_section_arguments( 'purge_cdn_cache_section', true );

		$this->assertSame( self::HELP, $args['help'] );
		$this->assertArrayNotHasKey( 'hide_need_help', $args );
	}
}
