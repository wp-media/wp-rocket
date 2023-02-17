<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\WPThemeTestcase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * @covers \WP_Rocket\ThirdParty\Divi::maybe_disable_youtube_preview
 *
 * @group ThirdParty
 */
class Test_MaybeDisableYoutubePreview extends WPThemeTestcase {
	use DBTrait;

	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/maybeDisableYoutubePreview.php';
	private static $container;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installFresh();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public static function tear_down_after_class() {
		self::uninstallAll();

		parent::tear_down_after_class();

		self::$container->get( 'event_manager' )->remove_subscriber( self::$container->get( 'divi' ) );
	}

	public function set_up() {
		parent::set_up();

		self::$container->get( 'event_manager' )->add_subscriber( self::$container->get( 'divi' ) );
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testSetsCorrectOptions( $config, $expected ) {

		if ( ! $config['template'] ) {
			$this->set_theme( $config['stylesheet'], $config['stylesheet'] );
		} else {
			$this->set_theme( $config['template'], $config['stylesheet'] )
				->set_child_theme( $config['stylesheet'], $config['stylesheet'], $config['template'] );
		}

		$options     = self::$container->get( 'options' );
		$options_api = self::$container->get( 'options_api' );
		$delayjs_html = self::$container->get( 'delay_js_html' );
		$options_api->set( 'settings', [] );

		$divi        = new Divi( $options_api, $options, $delayjs_html );

		switch_theme( $config['stylesheet'] );

		$divi->maybe_disable_youtube_preview( $config['stylesheet'], $this->theme );

		$this->assertSame( $expected['settings'], $options_api->get( 'settings' ) );
	}
}
