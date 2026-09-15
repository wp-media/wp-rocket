<?php

namespace WP_Rocket\Tests\Unit\inc\functions\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_need_api_key
 *
 * @group Functions
 * @group Admin
 */
class Test_RocketNeedApiKey extends TestCase {
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		if ( ! defined( 'WP_ROCKET_PLUGIN_NAME' ) ) {
			define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
		}

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/admin.php';
	}

	public function setUp(): void {
		parent::setUp();

		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
		Functions\when( 'wp_kses_post' )->returnArg();
	}

	public function testShouldExpandSentinelToTranslatedMessage() {
		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_check_key_errors' )
			->andReturn( [ 'invalid_license_data' ] );

		ob_start();
		rocket_need_api_key();
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'invalid_license_data', $output );
		$this->assertStringContainsString( 'The provided license data are not valid.', $output );
		$this->assertStringContainsString( 'contact support', $output );
	}

	public function testShouldEchoServerReturnedStringUnchanged() {
		$server_message = 'License validation failed. This user account is blocked.';

		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_check_key_errors' )
			->andReturn( [ $server_message ] );

		ob_start();
		rocket_need_api_key();
		$output = ob_get_clean();

		$this->assertStringContainsString( $server_message, $output );
		$this->assertStringNotContainsString( 'The provided license data are not valid.', $output );
	}
}
