<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPXCloud;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\WPXCloud;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\WPXCloud::append_cache_control_header
 *
 * @group  WPXCloud
 */
class Test_AppendCacheControlHeader extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $expected ) {
		$wpx_cloud = new WPXCloud();

		$this->assertSame(
			$expected,
			$wpx_cloud->append_cache_control_header()
		);
	}
}
