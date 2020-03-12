<?php

namespace WP_Rocket\Tests\Unit\inc\classes\admin\settings\Settings;

use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Settings\Settings;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Admin\Settings::sanitize_callback
 * @group  Settings
 */
class Test_SanitizeCallback extends TestCase {
	private $options;
	private $settings;

	public function setUp() {
		parent::setUp();

		$this->options  = $this->createMock( Options::class );
		$this->settings = new Settings( $this->options );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldSanitizeDNSPrefetchEntries( $input, $expected ) {
		Functions\when( 'esc_url_raw' )->alias( function( $url ) {
			if ( false === strpos( $url, ':' ) ) {
				$url = 'http:' . $url;
			}

			return filter_var( $url, FILTER_VALIDATE_URL );
		} );
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component ) {
			return parse_url( $url, $component );
		} );
		Functions\when( 'rocket_valid_key' )->justReturn( true );

		$output = $this->settings->sanitize_callback( $input );

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
