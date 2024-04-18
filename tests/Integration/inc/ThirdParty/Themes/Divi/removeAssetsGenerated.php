<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\WPThemeTestcase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * Test class covering \WP_Rocket\ThirdParty\Divi::remove_assets_generated
 *
 * @group Themes
 */
class Test_RemoveAssetsGenerated extends WPThemeTestcase {
	use DBTrait;

	private $container;
	private $event;
	private $subscriber;

	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/removeAssetsGenerated.php';

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installFresh();
	}

	public static function tear_down_after_class() {
		self::uninstallAll();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->container = apply_filters( 'rocket_container', '' );
		$this->event = $this->container->get( 'event_manager' );
	}

	public function tear_down() {
		$this->event->remove_subscriber( $this->subscriber );

		parent::tear_down();
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testShouldRemoveCallback( $config, $expected ) {
		$options     = $this->container->get( 'options' );
		$options_api = $this->container->get( 'options_api' );
		$delayjs_html = $this->container->get( 'delay_js_html' );
		$used_css = $this->container->get( 'rucss_used_css_controller' );
		$options_api->set( 'settings', [] );
		$this->subscriber = new Divi( $options_api, $options, $delayjs_html, $used_css );

		$this->event->add_subscriber( $this->subscriber );

		add_action( 'et_dynamic_late_assets_generated', '__return_true' );
		$this->assertTrue( has_action( 'et_dynamic_late_assets_generated' ) );

		switch_theme( $config['stylesheet'] );

		do_action( 'after_setup_theme' );

		$this->assertSame(
			$expected,
			has_action( 'et_dynamic_late_assets_generated' )
		);
	}

}
