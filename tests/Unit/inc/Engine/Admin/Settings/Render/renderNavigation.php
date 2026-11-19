<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Render;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Model\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Render::render_navigation
 *
 * @group Admin
 * @group SettingsPage
 */
class Test_RenderNavigation extends TestCase {
	/**
	 * Tests that the navigation items are arranged into groups and standalone items.
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected entries passed to the navigation template.
	 *
	 * @dataProvider configTestData
	 */
	public function testShouldArrangeNavigationIntoEntries( $config, $expected ) {
		Functions\stubTranslationFunctions();
		Functions\stubs(
			[
				'sanitize_html_class' => null,
				'wp_parse_args'       => function ( $args, $defaults ) {
					return array_merge( $defaults, $args );
				},
			]
		);

		$render = Mockery::mock( Render::class, [ '', Mockery::mock( PluginFamily::class ) ] )->makePartial();
		$render->set_settings( $config['navigation'] );
		$render->shouldReceive( 'generate' )
			->once()
			->with( 'navigation', $expected )
			->andReturn( '' );

		$render->render_navigation();
	}
}
