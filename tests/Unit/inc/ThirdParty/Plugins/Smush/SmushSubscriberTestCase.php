<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Smush;

use Brain\Monkey\Functions;
use Mockery;
use Smush\Core\Settings;
use WPMedia\PHPUnit\Unit\TestCase;

abstract class SmushSubscriberTestCase extends TestCase {

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/Smush/SmushCoreSettings.php';
	}

	protected function mock_is_smush_lazyload_enabled( $lazyload_enabled, array $lazyload_formats ) {
		$settings = Settings::get_instance();

		$settings->set_settings( $lazyload_enabled, $lazyload_formats );

		return $settings;
	}
}
