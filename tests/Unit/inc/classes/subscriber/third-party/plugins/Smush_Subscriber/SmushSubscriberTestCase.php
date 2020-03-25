<?php
namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Smush_Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;

abstract class SmushSubscriberTestCase extends TestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_SMUSH_PREFIX' )
			->andReturn( 'wp-smush-' );

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/classes/subscriber/third-party/plugins/Smush_Subscriber/SmushCoreSettings.php';
	}

	protected function mock_is_smush_lazyload_enabled( $lazyload_enabled, array $lazyload_formats ) {
		$settings = \Smush\Core\Settings::get_instance();

		$settings->set_settings( $lazyload_enabled, $lazyload_formats );

		return $settings;
	}
}
