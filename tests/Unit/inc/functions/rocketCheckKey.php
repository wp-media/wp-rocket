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

	public function testShouldReturnTrueWhenValidKey() {
		Functions\expect( 'rocket_valid_key' )->once()->andReturn( true );
		Functions\expect( 'rocket_delete_licence_data_file' )->once();
		Functions\expect( 'wp_remote_get' )->never();

		$this->assertTrue( rocket_check_key() );
	}

	public function testShouldReturnArrayWhenSuccessfulValidation() {
		Functions\expect( 'rocket_valid_key' )->once()->andReturn( false );
		Functions\expect( 'rocket_delete_licence_data_file' )->never();
		Functions\expect( 'wp_remote_get' )
			->once()
			->with( 'https://api.wp-rocket.me/valid_key.php', [ 'timeout' => 30 ] )
			->andReturn( [] );
		Functions\expect( 'is_wp_error' )->once()->andReturn( false );
		Functions\expect( 'wp_remote_retrieve_body' )
			->once()
			->with( [] )
			->andReturn( '{"success": true, "data":{"consumer_key":"ABCDEF","consumer_email":"example@example.org","secret_key":"secret"}}' );
		Functions\expect( 'get_rocket_option' )->once()->with( 'license' )->andReturn( true );
		Functions\expect( 'set_transient' )
			->once()
			->with(
				'wp_rocket_settings',
				[
					'consumer_key'   => 'ABCDEF',
					'consumer_email' => 'example@example.org',
					'secret_key'     => 'secret',
				]
			)
			->andReturn( true );
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocket_check_key_errors' )
			->andReturn( true );
		Functions\expect( 'rocket_delete_licence_data_file' )->once();
		Functions\expect( 'update_option' )
			->with( 'wp_rocket_no_licence', 0 )
			->once();
		$expected = [
			'consumer_key'   => 'ABCDEF',
			'consumer_email' => 'example@example.org',
			'secret_key'     => 'secret',
		];

		$this->assertSame( $expected, rocket_check_key() );
	}

	public function testShouldReturnFalseWhenIsWPError() {
		Functions\when( 'rocket_valid_key' )->justReturn( false );
		Functions\when( 'wp_remote_get' )->alias( function() {
			$wp_error = \Mockery::mock( \WP_Error::class )->makePartial();
			$wp_error->shouldReceive( 'get_error_messages' )
			         ->andReturn( 'error' );

			return $wp_error;
		} );
		Functions\when( 'is_wp_error' )->justReturn( true );
		Functions\when( 'set_transient' )->justReturn( true );
		Functions\expect( 'rocket_delete_licence_data_file' )
			->never();
		Functions\expect( 'update_option' )
			->never();

		$this->assertFalse( rocket_check_key() );
	}

	public function testShouldReturnFalseWhenEmptyResponse() {
		Functions\when( 'rocket_valid_key' )->justReturn( false );
		Functions\when( 'wp_remote_get' )->justReturn( [] );
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '' );
		Functions\when( 'set_transient' )->justReturn( true );
		Functions\expect( 'rocket_delete_licence_data_file' )->never();
		Functions\expect('update_option')
			->never();

		$this->assertFalse( rocket_check_key() );
	}

	public function testShouldReturnArrayWhenSuccessFalse() {
		Functions\when( 'rocket_valid_key' )->justReturn( false );
		Functions\when( 'wp_remote_get' )->justReturn( [] );
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '{"success": false, "data":{"consumer_key":"ABCDEF","consumer_email":"example@example.org","reason":"BAD_KEY"}}' );
		Functions\when( 'set_transient' )->justReturn( true );
		Functions\expect( 'rocket_delete_licence_data_file' )->never();
		Functions\expect( 'update_option' )
			->never();

		$expected = [
			'consumer_key'   => 'ABCDEF',
			'consumer_email' => 'example@example.org',
			'secret_key'     => '',
		];

		$this->assertSame( $expected, rocket_check_key() );
	}
}
