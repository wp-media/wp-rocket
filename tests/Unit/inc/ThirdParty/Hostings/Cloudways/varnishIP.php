<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Cloudways;

use WP_Rocket\ThirdParty\Hostings\Cloudways;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Cloudways::varnish_ip
 * @group Cloudways
 * @group ThirdParty
 */
class Test_VarnishIP extends TestCase {
	public function tearDown() {
		parent::tearDown();

		// Reset after each test.
		unset( $_SERVER['HTTP_X_VARNISH'] );
		unset( $_SERVER['HTTP_X_APPLICATION'] );
	}
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnCloudwaysVarnishIP( $varnish_ip, $config_server, $expected ) {
		if ( isset( $config_server['HTTP_X_VARNISH'] ) ) {
			$_SERVER['HTTP_X_VARNISH'] = $config_server['HTTP_X_VARNISH'];
		}
		if ( isset( $config_server['HTTP_X_APPLICATION'] ) ) {
			$_SERVER['HTTP_X_APPLICATION'] = $config_server['HTTP_X_APPLICATION'];
		}

		$cloudways = new Cloudways();

		$this->assertSame(
			$expected,
			$cloudways->varnish_ip( $varnish_ip )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'varnishIP' );
	}
}
