<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_check_key
 *
 * @group Functions
 * @group Options
 */
class Test_RocketCheckKey extends TestCase {
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php';
	}

	public function setUp(): void {
		parent::setUp();

		Functions\stubTranslationFunctions();
	}

	public function testShouldReturnLocalValidationDataWithoutRemoteRequest() {
		Functions\expect( 'wp_remote_get' )->never();
		Functions\expect( 'get_rocket_option' )->once()->with( 'license' )->andReturn( true );
		Functions\expect( 'set_transient' )
			->once()
			->with(
				'wp_rocket_settings',
				[
					'consumer_key'   => '',
					'consumer_email' => '',
					'secret_key'     => 'local-build',
				]
			)
			->andReturn( true );
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocket_check_key_errors' )
			->andReturn( true );
		Functions\expect( 'update_option' )
			->with( 'wp_rocket_no_licence', 0 )
			->once();
		$expected = [
			'consumer_key'   => '',
			'consumer_email' => '',
			'secret_key'     => 'local-build',
		];

		$this->assertSame( $expected, rocket_check_key() );
	}

	public function testShouldSetLicenseFlagWhenMissing() {
		Functions\expect( 'wp_remote_get' )->never();
		Functions\expect( 'get_rocket_option' )->once()->with( 'license' )->andReturn( false );
		Functions\expect( 'set_transient' )
			->once()
			->with(
				'wp_rocket_settings',
				[
					'consumer_key'   => '',
					'consumer_email' => '',
					'secret_key'     => 'local-build',
					'license'        => '1',
				]
			)
			->andReturn( true );
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocket_check_key_errors' )
			->andReturn( true );
		Functions\expect( 'update_option' )
			->with( 'wp_rocket_no_licence', 0 )
			->once();

		$expected = [
			'consumer_key'   => '',
			'consumer_email' => '',
			'secret_key'     => 'local-build',
			'license'        => '1',
		];

		$this->assertSame( $expected, rocket_check_key() );
	}
}
