<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Avada;

/**
 * @covers \WP_Rocket\ThirdParty\Avada::maybe_deactivate_lazyload
 *
 * @group  AvadaTheme
 * @group  ThirdParty
 */
class Test_MaybeDeactivateLazyLoad extends TestCase {
	protected      $path_to_test_data = '/inc/ThirdParty/Themes/Avada/maybeDeactivateLazyLoad.php';
	private static $container;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testMaybeDeactivateLazyloadWhenActiveInAvada( $old_value, $value, $expected ) {
		$options     = self::$container->get( 'options' );
		$options_api = self::$container->get( 'options_api' );
		$options_api->set( 'settings', [] );

		apply_filters( 'update_option_fusion_options', $old_value, $value );

		$this->assertSame( $expected, $options_api->get( 'settings' ) );
	}
}
