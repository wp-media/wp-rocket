<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\WPThemeTestcase;

/**
 * @covers \WP_Rocket\ThirdParty\Divi::disable_image_dimensions_height_percentage
 *
 * @group  ThirdParty
 */
class Test_DisableImageDimensionsHeightPercentage extends WPThemeTestcase {
	use DBTrait;

	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/Integration/disableImageDimensionsHeightPercentage.php';

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
		add_filter( 'rocket_specify_image_dimensions', '__return_true' );
	}

	public function tear_down() {
		remove_filter( 'rocket_specify_image_dimensions', '__return_true' );
		unset( $GLOBALS['wp'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testRemovesCorrectImagesFromAddDimensionsArray( $config, $expected, $html ) {
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
