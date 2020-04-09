<?php
namespace WP_Rocket\Tests\Unit\inc\vendors\ipInRange;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::rocket_ip2long6
 * @group inInRange
 * @group vendors
 */
class Test_RocketIp2Long6 extends TestCase {
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/vendors/ip_in_range.php';
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldConvertIP6( $ip, $converted ) {
		$this->assertSame( $converted, rocket_ip2long6( $ip ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rocketIp2Long6' );
	}
}
