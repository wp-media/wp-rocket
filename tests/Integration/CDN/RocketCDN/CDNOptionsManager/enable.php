<?php

namespace WP_Rocket\Tests\Integration\CDN\RocketCDN\CDNOptionsManager;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers\WP_Rocket\CDN\RocketCDN\CDNOptionsManager::enable
 * @group RocketCDN
 */
class Test_Enable extends TestCase {

	public function testShouldEnableCDNOptions() {
		set_transient( 'rocketcdn_status', [ 'transient' ], MINUTE_IN_SECONDS );

		$options      = new Options( 'wp_rocket_' );
		$option_array = new Options_Data( $options->get( 'settings' ) );

		( new CDNOptionsManager(
				$options,
				$option_array
			) )->enable( 'https://rocketcdn.me' );

		$expected_subset = [
			'cdn'        => 1,
			'cdn_cnames' => [ 'https://rocketcdn.me' ],
			'cdn_zone'   => [ 'all' ],
		];

		$options = get_option( 'wp_rocket_settings' );

		foreach ( $expected_subset as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[$key] );
		}

		$this->assertFalse( get_transient( 'rocketcdn_status' ) );
	}
}
