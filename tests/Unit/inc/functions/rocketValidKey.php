<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_valid_key
 *
 * @group Functions
 * @group Options
 */
class Test_RocketValidKey extends TestCase {
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php';
	}

	public function testShouldReturnFalseWhenNoSecretKey() {
		Functions\expect( 'get_rocket_option' )
			->once()
			->with( 'secret_key', '' )
			->andReturn( '' );
		Functions\expect( 'set_transient' )->never();

		$this->assertFalse( rocket_valid_key() );
	}

	public function testShouldStoreSentinelWithoutTranslatingWhenInvalidLicenseData() {
		Functions\when( 'get_rocket_option' )->alias(
			function ( $option ) {
				switch ( $option ) {
					case 'secret_key':
						return 'not_a_matching_secret';
					case 'consumer_key':
						return 'tooshort';
					case 'consumer_email':
						return 'example@example.org';
					default:
						return '';
				}
			}
		);

		// The sentinel must be stored, not a translated sentence.
		Functions\expect( 'set_transient' )
			->once()
			->with( 'rocket_check_key_errors', [ 'invalid_license_data' ] )
			->andReturn( true );

		// No eager translation must happen during this early call.
		Functions\expect( '__' )->never();
		Functions\expect( '_n' )->never();

		$this->assertFalse( rocket_valid_key() );
	}

	public function testShouldReturnTrueWhenLicenseDataIsValid() {
		$consumer_email = 'example@example.org';
		$secret_key     = hash( 'crc32', $consumer_email );

		Functions\when( 'get_rocket_option' )->alias(
			function ( $option ) use ( $consumer_email, $secret_key ) {
				switch ( $option ) {
					case 'secret_key':
						return $secret_key;
					case 'consumer_key':
						return '12345678'; // 8 characters.
					case 'consumer_email':
						return $consumer_email;
					default:
						return '';
				}
			}
		);

		Functions\expect( 'set_transient' )->never();

		$this->assertTrue( rocket_valid_key() );
	}
}
