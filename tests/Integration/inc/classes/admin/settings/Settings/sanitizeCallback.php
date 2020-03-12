<?php

namespace WP_Rocket\Tests\Integration\inc\classes\admin\settings\Settings;

use WPMedia\PHPUnit\Integration\AdminTestCase;

/**
 * @covers \WP_Rocket\Admin\Settings::sanitize_callback
 * @group  Settings
 * @group  AdminOnly
 */
class Test_SanitizeCallback extends AdminTestCase {
	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldSanitizeDNSPrefetchEntries( $input, $expected ) {
		$output = apply_filters( 'sanitize_option_wp_rocket_settings', $input );

		$this->assertArrayHasKey( 'dns_prefetch', $output );
		$this->assertSame(
			$expected['dns_prefetch'],
			array_values( $output['dns_prefetch'] )
		);
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'dns-prefetch' );
	}
}
