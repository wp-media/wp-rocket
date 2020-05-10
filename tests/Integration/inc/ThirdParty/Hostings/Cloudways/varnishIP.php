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
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnCloudwaysVarnishIP( $varnish_ip, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_varnish_ip', $varnish_ip )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'varnishIP' );
	}
}
