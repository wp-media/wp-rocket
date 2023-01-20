<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Jevelin;

use WP_Rocket\Tests\Integration\WPThemeTestcase;
use WP_Rocket\ThirdParty\Themes\Jevelin;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Jevelin::preserve_patterns
 * @group Jevelin
 * @group ThirdParty
 */
class Test_PreservePatterns extends WPThemeTestcase {
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Jevelin/integrations/preservePatterns.php';
	private static $container;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::$container->get( 'event_manager' )->remove_subscriber( self::$container->get( 'wp_rocket.thirdparty.themes.serviceprovider.jevelin' ) );
	}

	public function set_up() {
		parent::set_up();

		self::$container->get( 'event_manager' )->add_subscriber( self::$container->get( 'wp_rocket.thirdparty.themes.serviceprovider.jevelin' ) );
	}
	/**
	 * @dataProvider ProviderTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( ! $config['is-child'] ) {
			$this->set_theme( $config['stylesheet'], $config['theme-name'] );
		} else {
			$this->set_theme( $config['is-child'], $config['parent-name'] )
				->set_child_theme( $config['stylesheet'], $config['theme-name'], $config['is-child'] );
		}

		$jevelin = new Jevelin();

        $result = $jevelin->preserve_patterns( $config['patterns'] );

		$this->assertSame( $expected, $result );
	}
}
