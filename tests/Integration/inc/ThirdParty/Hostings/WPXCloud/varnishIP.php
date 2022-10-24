<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WPXCloud;

use WP_Rocket\ThirdParty\Hostings\WPXCloud;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WPXCloud::varnish_ip
 * @group WPXCloud
 */
class Test_VarnishIP extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnWPXCloudVarnishIP( $varnish_ip, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_varnish_ip', $varnish_ip )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'varnishIP' );
	}
}
