<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\WPThemeTestcase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * @covers \WP_Rocket\ThirdParty\Divi::disable_divi_jquery_body
 *
 * @group Themes
 */
class Test_disableDiviJqueryBody extends WPThemeTestcase {
	private $container;
	private $event;
	private $subscriber;

	private $delay_js = false;
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/disableDiviJqueryBody.php';

	public function set_up() {
		parent::set_up();

		$this->container = apply_filters( 'rocket_container', '' );
		$this->event = $this->container->get( 'event_manager' );

		define( 'ET_CORE_VERSION','4.10');
	}

	public function tear_down() {
		$this->event->remove_subscriber( $this->subscriber );

		$this->delay_js = false;
		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );
		unset( $GLOBALS['ET_CORE_VERSION'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testDisableDiviJqueryBody( $config, $expected ) {
		$this->delay_js = true;
		$options     = $this->container->get( 'options' );
		$options_api = $this->container->get( 'options_api' );
		$delayjs_html = $this->container->get( 'delay_js_html' );
		$used_css = $this->container->get( 'rucss_used_css_controller' );
		$options_api->set( 'settings', [] );
		$this->subscriber = new Divi( $options_api, $options, $delayjs_html, $used_css );

		$this->event->add_subscriber( $this->subscriber );

		add_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );

		$this->set_theme( $config['stylesheet'], $config['theme-name'] );

		$this->subscriber->disable_divi_jquery_body();

		$this->assertSame(
			$expected['filter_priority'],
			has_filter( 'et_builder_enable_jquery_body', '__return_false' )
		);
	}

	public function set_delay_js_option() {
		return $this->delay_js;
	}
}
