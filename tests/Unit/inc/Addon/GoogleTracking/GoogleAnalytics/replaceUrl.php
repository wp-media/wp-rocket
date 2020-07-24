<?php
namespace WP_Rocket\Tests\Unit\inc\Addon\GoogleTracking\GoogleAnalytics;

use WP_Rocket\Addon\GoogleTracking\GoogleAnalytics;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Addon\GoogleTracking\GoogleAnalytics::replace_url
 * @group  Addon
 * @group  GoogleTracking
 */
class Test_ReplaceUrl extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Addon/GoogleTracking/GoogleAnalytics/replaceUrl.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReplaceUrl( $config, $expected ) {
		$html = isset( $config['html'] ) ? $config['html'] : '';
		$url  = isset( $config['url'] ) ? $config['url']   : '';

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		if ( ! empty( $url ) ) {
			Functions\expect( 'wp_remote_get' )->once()->with( $url )->andReturn( 'request' );
			Functions\expect( 'wp_remote_retrieve_body' )->once()->with( 'request' )->andReturn( 'Remote File contents here' );
		}


		$busting_path     = WP_ROCKET_CACHE_ROOT_PATH . 'busting/';
		$busting_url      = WP_ROCKET_CACHE_ROOT_URL . 'busting/';
		$google_analytics = new GoogleAnalytics( $busting_path, $busting_url );
		$actual           = $google_analytics->replace_url( $html );

		$this->assertEquals( str_replace( '{HOME_URL}', 'vfs://public', $expected ), $actual );
	}

}
