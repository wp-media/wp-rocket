<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\CDNOptionsManager;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::disable
 * @uses :rocket_clean_domain
 * @uses \WP_Rocket\Admin\Options_Data::set
 * @uses \WP_Rocket\Admin\Options::set
 * @uses \WP_Rocket\Admin\Options::get_option_name
 *
 * @group RocketCDN
 */
class Test_Disable extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CDN/RocketCDN/CDNOptionsManager/enable_disable.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisableCDNOptions( $cleanedUrls ) {
        add_option( 'rocketcdn_user_token', '123456' );
		set_transient( 'rocketcdn_status', [ 'transient' ], MINUTE_IN_SECONDS );

		$this->generateEntriesShouldExistAfter( $cleanedUrls );

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

		$this->checkEntriesDeleted( $cleanedUrls );

        $this->assertFalse( get_option( 'rocketcdn_user_token' ) );
		$this->assertFalse( get_transient( 'rocketcdn_status' ) );
	}
}
