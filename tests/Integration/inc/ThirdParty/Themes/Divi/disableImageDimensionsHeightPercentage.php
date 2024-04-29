<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\WPThemeTestcase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Divi::disable_image_dimensions_height_percentage()
 *
 * @group Themes
 */
class Test_DisableImageDimensionsHeightPercentage extends WPThemeTestcase {
	use DBTrait;

	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/Integration/disableImageDimensionsHeightPercentage.php';

	private $container;
	private $event;
	private $subscriber;

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

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'specify_image_dimensions', 17 );

		add_filter( 'rocket_specify_image_dimensions', '__return_true' );
	}

	public function tear_down() {
		$this->event->remove_subscriber( $this->subscriber );

		$this->restoreWpHook( 'rocket_buffer' );

		remove_filter( 'rocket_specify_image_dimensions', '__return_true' );
		unset( $GLOBALS['wp'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testRemovesCorrectImagesFromAddDimensionsArray( $config, $expected, $html ) {
		$options     = $this->container->get( 'options' );
		$options_api = $this->container->get( 'options_api' );
		$delayjs_html = $this->container->get( 'delay_js_html' );
		$used_css = $this->container->get( 'rucss_used_css_controller' );
		$options_api->set( 'settings', [] );
		$this->subscriber = new Divi( $options_api, $options, $delayjs_html, $used_css );

		$this->event->add_subscriber( $this->subscriber );

		$GLOBALS['wp'] = (object) [
			'query_vars' => [],
			'request'    => 'http://example.org'
		];

		if ( ! $config['is-child'] ) {
			$this->set_theme( $config['stylesheet'], $config['theme-name'] );
		} else {
			$this->set_theme( $config['is-child'], $config['parent-name'] )
				->set_child_theme( $config['stylesheet'], $config['theme-name'], $config['is-child'] );
		}

		switch_theme( $config['stylesheet'] );

		$this->assertSame(
			$this->format_the_html( $html['expected'] ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $html['original'] ) )
		);
	}
}
