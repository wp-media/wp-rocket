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
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnCloudwaysVarnishIP( $varnish_ip, $expected ) {
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
