<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\WPThemeTestcase;

/**
 * @covers \WP_Rocket\ThirdParty\Divi::disable_image_dimensions_height_percentage()
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

	public function tearDown() {
		if ( isset( $this->config_data['image_dimensions'] ) ){
			remove_filter( 'pre_get_rocket_option_image_dimensions', [$this, 'set_image_dimensions'] );
		}

		unset( $GLOBALS['wp'] );
		parent::tearDown();
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
		add_filter( 'rocket_specify_image_dimensions', '__return_true' );

		$this->assertSame(
			$this->format_the_html( $html['expected'] ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $html['original'] ) )
		);
	}
}
