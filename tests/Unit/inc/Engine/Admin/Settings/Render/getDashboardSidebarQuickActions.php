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
 * @covers \WP_Rocket\Engine\Admin\Settings\Render::get_dashboard_sidebar
 *
 * @group SettingsPage
 */
class Test_GetDashboardSidebarQuickActions extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRenderTheQuickActions( $config, $expected ) {
		Functions\stubTranslationFunctions();
		Functions\when( 'esc_url' )->alias(
			function ( $url ) {
				return 0 === strpos( $url, 'javascript:' ) ? '' : $url;
			}
		);

		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->once()->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_addons' )->once()->andReturn( [] );
		Filters\expectApplied( 'rocket_dashboard_sidebar_quick_actions' )->once()->with( [] )->andReturn( $config['actions'] );

		$render = Mockery::mock( Render::class, [ 'vfs://views', Mockery::mock( PluginFamily::class ) ] )->makePartial();

		if ( empty( $expected['items'] ) ) {
			$render->shouldReceive( 'generate' )->never();
		} else {
			// The Quick Actions section (card) is rendered around the category: pass the content through.
			$render->shouldReceive( 'generate' )
				->with( 'partials/sidebar/section', [ 'class' => 'wpr-Dashboard-sidebar-section--quickActions', 'content' => $expected['html'] ] )
				->once()
				->andReturnUsing(
					function ( $template, $data ) {
						return $data['content'];
					}
				);

			$contents = '';

			foreach ( $expected['items'] as $item ) {
				$render->shouldReceive( 'generate' )->once()->ordered()->with( 'partials/sidebar/quick-action-item', $item )->andReturn( '<i>item</i>' );
				$contents .= '<i>item</i>';
			}

			$render->shouldReceive( 'generate' )
				->once()
				->ordered()
				->with( 'partials/sidebar/category', [ 'title' => 'Quick Actions', 'content' => $contents, 'class' => 'wpr-Dashboard-sidebar-category--quickActions' ] )
				->andReturn( $expected['html'] );
		}

		$this->assertSame( $expected['html'], $render->get_dashboard_sidebar() );
	}
}
