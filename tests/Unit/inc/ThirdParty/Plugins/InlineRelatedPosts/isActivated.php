<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\InlineRelatedPosts;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts::is_activated
 *
 * @group InlineRelatedPosts
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests InlineRelatedPosts::is_activated() against the presence/absence of IRP_PLUGIN_SLUG.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['irp_plugin_slug'] ) {
			$this->constants['IRP_PLUGIN_SLUG'] = $config['irp_plugin_slug'];
		}

		$this->assertSame( $expected, InlineRelatedPosts::is_activated() );
	}
}
