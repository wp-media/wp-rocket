<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\WPThemeTestcase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * @covers \WP_Rocket\ThirdParty\Divi::remove_assets_generated
 * @uses   ::is_divi
 *
 * @group  ThirdParty
 */
class Test_RemoveAssetsGenerated extends WPThemeTestcase
{
	use DBTrait;

	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/removeAssetsGenerated.php';

	private static $container;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installFresh();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public static function tear_down_after_class() {
		self::uninstallAll();

		parent::tear_down_after_class();

		self::$container->get( 'event_manager' )->remove_subscriber( self::$container->get( 'wp_rocket.thirdparty.themes.serviceprovider.divi' ) );
	}

	public function set_up() {
		parent::set_up();

		self::$container->get( 'event_manager' )->add_subscriber( self::$container->get( 'wp_rocket.thirdparty.themes.serviceprovider.divi' ) );
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testShouldRemoveCallback( $config, $expected ) {
		add_action('et_dynamic_late_assets_generated', '__return_true');
		$this->assertTrue(has_action('et_dynamic_late_assets_generated'));
		$options     = self::$container->get( 'options' );
		$options_api = self::$container->get( 'options_api' );
		$delayjs_html = self::$container->get( 'wp_rocket.engine.optimization.delayjs.serviceprovider.delay_js_html' );
		$options_api->set( 'settings', [] );

		$divi        = new Divi( $options_api, $options, $delayjs_html );

		switch_theme( $config['stylesheet'] );
		$divi->remove_assets_generated();
		$this->assertSame($expected, has_action('et_dynamic_late_assets_generated'));
	}

}
