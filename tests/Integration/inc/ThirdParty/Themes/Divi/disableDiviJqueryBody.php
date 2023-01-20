<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\WPThemeTestcase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * @covers \WP_Rocket\ThirdParty\Divi::disable_divi_jquery_body
 * @uses   ::is_divi
 *
 * @group  ThirdParty
 */
class Test_disableDiviJqueryBody extends WPThemeTestcase {
	private $delay_js = false;
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/disableDiviJqueryBody.php';

	private static $container;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::$container->get( 'event_manager' )->remove_subscriber( self::$container->get( 'wp_rocket.thirdparty.themes.serviceprovider.divi' ) );
	}

	public function set_up() {
		parent::set_up();

		self::$container->get( 'event_manager' )->add_subscriber( self::$container->get( 'wp_rocket.thirdparty.themes.serviceprovider.divi' ) );
		define( 'ET_CORE_VERSION','4.10');
	}

	public function tear_down() : void {
		parent::tear_down();
		$this->delay_js = false;
		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );
		unset( $GLOBALS['ET_CORE_VERSION'] );
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testDisableDiviJqueryBody($config, $expected ) {

		$this->delay_js            = true;

		add_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );

		$this->set_theme( $config['stylesheet'], $config['theme-name'] );
		$options     = self::$container->get( 'options' );
		$options_api = self::$container->get( 'options_api' );
		$delayjs_html = self::$container->get( 'wp_rocket.engine.optimization.delayjs.serviceprovider.delay_js_html' );
		$options_api->set( 'settings', [] );
		$divi        = new Divi( $options_api, $options, $delayjs_html );
		$divi->disable_divi_jquery_body();
		$this->assertSame( $expected['filter_priority'], has_filter( 'et_builder_enable_jquery_body', '__return_false' ) );
	}

	public function set_delay_js_option() {
		return $this->delay_js;
	}
}
