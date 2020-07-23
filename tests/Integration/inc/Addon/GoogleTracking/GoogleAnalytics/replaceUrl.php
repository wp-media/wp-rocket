<?php
namespace WP_Rocket\Tests\Integration\inc\Addon\GoogleTracking\GoogleAnalytics;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Addon\GoogleTracking\GoogleAnalytics::replace_url
 * @group  Addon
 * @group  GoogleTracking
 */
class Test_ReplaceUrl extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Addon/GoogleTracking/GoogleAnalytics/replaceUrl.php';

	public function tearDown() {
		parent::tearDown();
		remove_filter('pre_get_rocket_option_google_analytics_cache', '__return_true');
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReplaceUrl( $config, $expected ) {
		$html = isset( $config['html'] ) ? $config['html'] : '';
		$url  = isset( $config['url'] ) ? $config['url'] : '';

		if ( ! empty( $url ) ) {
			Functions\expect( 'wp_remote_get' )->once()->with( $url )->andReturn( 'request' );
			Functions\expect( 'wp_remote_retrieve_body' )->once()->with( 'request' )->andReturn( 'Remote File contents here' );
		}

		add_filter('pre_get_rocket_option_google_analytics_cache', '__return_true');

		$actual = apply_filters( 'rocket_buffer', $html );

		$this->assertEquals( str_replace( '{HOME_URL}', 'http://example.org', $expected ), $actual );
	}

}
