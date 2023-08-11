<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\WPThemeTestcase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Divi::disable_dynamic_css_on_rucss
 * @uses   ::is_divi
 *
 * @group  ThirdParty
 */
class Test_DisableDynamicCssOnRucss extends WPThemeTestcase {
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/disableDynamicCssOnRucss.php';
	private static $container;
	private $rucss_enable = false;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public function set_up() {
		parent::set_up();
		add_filter( 'pre_get_rocket_option_remove_unused_css', [$this, 'get_rucss_value'] );
	}

	public function tear_down()
	{
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [$this, 'get_rucss_value'] );
		parent::tear_down();
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->set_theme( $config['stylesheet'], $config['theme-name'] );
		$this->rucss_enable = $config['rucss_enabled'];
		$options     = self::$container->get( 'options' );
		$options_api = self::$container->get( 'options_api' );
		$delayjs_html = self::$container->get( 'delay_js_html' );
		$used_css = self::$container->get( 'rucss_used_css_controller' );
		$options_api->set( 'settings', [] );
		$divi        = new Divi( $options_api, $options, $delayjs_html, $used_css );
		$divi->disable_dynamic_css_on_rucss();
		$this->assertSame($expected, has_filter( 'et_use_dynamic_css', '__return_false' ));
	}

	public function get_rucss_value() {
		return $this->rucss_enable;
	}
}
