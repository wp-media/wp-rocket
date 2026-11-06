<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPXCloud;

use WP_Rocket\ThirdParty\Hostings\WPXCloud;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\WPXCloud::varnish_ip
 * @group WPXCloud
 */
class Test_VarnishIP extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnWPXCloudVarnishIP( $varnish_ip, $expected ) {

		$wpx_cloud = new WPXCloud();

		$this->assertSame(
			$expected,
			$wpx_cloud->varnish_ip( $varnish_ip )
		);
	}
}
