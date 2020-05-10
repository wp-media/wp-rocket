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
	public function testShouldReturnCloudwaysVarnishIP() {
		$cloudways = new Cloudways();

		$varnish_ip = [];
		$expected   = [
			'127.0.0.1:8080',
		];

		$this->assertSame(
			$expected,
			$cloudways->varnish_ip( $varnish_ip )
		);
	}
}
