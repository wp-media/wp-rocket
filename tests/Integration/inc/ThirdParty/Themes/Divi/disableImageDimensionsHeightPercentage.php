<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\WPThemeTestcase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * @covers \WP_Rocket\ThirdParty\Divi::disable_image_dimensions_height_percentage()
 * @uses   ::is_divi
 *
 * @group  ThirdParty
 */
class Test_DisableImageDimensionsHeightPercentage extends WPThemeTestcase {
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/disableImageDimensionsHeightPercentage.php';
	private static $container;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::$container->get( 'event_manager' )->remove_subscriber( self::$container->get( 'divi' ) );
	}

	public function setUp() {
		parent::setUp();

		self::$container->get( 'event_manager' )->add_subscriber( self::$container->get( 'divi' ) );
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testRemovesCorrectImagesFromAddDimensionsArray( $config, $expected ) {

		if ( ! $config['is-child'] ) {
			$this->set_theme( $config['stylesheet'], $config['theme-name'] );
		} else {
			$this->set_theme( $config['is-child'], $config['parent-name'] )
				->set_child_theme( $config['stylesheet'], $config['theme-name'], $config['is-child'] );
		}

		$options     = self::$container->get( 'options' );
		$options_api = self::$container->get( 'options_api' );
		$options_api->set( 'settings', [] );

		$divi        = new Divi( $options_api, $options );

		switch_theme( $config['stylesheet'] );

		$images = $divi->disable_image_dimensions_height_percentage( $config['images'] );

		$this->assertSame( $expected, $images );
	}
}
