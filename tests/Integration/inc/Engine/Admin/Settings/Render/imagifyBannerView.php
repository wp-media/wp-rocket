<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Render;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering views/settings/partials/imagify-banner.php and imagify-banner-enabled.php
 *
 * @uses \WP_Rocket\Abstract_Render::generate
 *
 * @group AdminOnly
 * @group SettingsPage
 */
class Test_ImagifyBannerView extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRenderMixpanelDataAttributes( $config, $expected ) {
		$container = apply_filters( 'rocket_container', null );
		$render    = $container->get( 'settings_render' );

		$html = $render->generate( $config['template'], [ 'title' => 'Image & Media' ] );

		foreach ( $expected['contains'] as $needle ) {
			$this->assertStringContainsString( $needle, $html );
		}

		foreach ( $expected['not_contains'] as $needle ) {
			$this->assertStringNotContainsString( $needle, $html );
		}
	}
}
