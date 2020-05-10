<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Cloudways;

use WP_Rocket\ThirdParty\Hostings\Cloudways;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Cloudways::varnish_ip
 * @group Cloudways
 * @group ThirdParty
 */
class Test_VarnishIP extends TestCase {
	public function testShouldReturnCloudwaysVarnishIP() {
		$varnish_ip = [];
		$expected   = [
			'127.0.0.1:8080',
		];

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_varnish_ip', $varnish_ip )
		);
	}
}
