<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\CDNOptionsManager;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::disable
 * @uses \WP_Rocket\Admin\Options_Data::set
 * @uses \WP_Rocket\Admin\Options::set
 * @uses \WP_Rocket\Admin\Options::get_option_name
 *
 * @group RocketCDN
 */
class Test_Disable extends TestCase {

	public function testShouldDisableCDNOptions() {
        add_option( 'rocketcdn_user_token', '123456' );
		set_transient( 'rocketcdn_status', [ 'transient' ], MINUTE_IN_SECONDS );

		$options      = new Options( 'wp_rocket_' );
		$option_array = new Options_Data( $options->get( 'settings' ) );

		( new CDNOptionsManager(
				$options,
				$option_array
			) )->disable();

		$expected_subset = [
			'cdn'        => 0,
			'cdn_cnames' => [],
			'cdn_zone'   => [],
		];

		$options = get_option( 'wp_rocket_settings' );

		foreach ( $expected_subset as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[$key] );
		}

        $this->assertFalse( get_option( 'rocketcdn_user_token' ) );
		$this->assertFalse( get_transient( 'rocketcdn_status' ) );
	}
}
