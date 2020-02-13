<?php
namespace WP_Rocket\Tests\Unit\Addons\Cloudflare;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use Brain\Monkey\Functions;

/**
 * @group Cloudflare
 */
class TestSetMinify extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * Test set minify with cached invalid transient.
	 */
	public function testSetMinifyWithInvalidCredentials() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( $wp_error );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );

		$this->assertEquals(
			$wp_error,
			$cloudflare->set_minify( 'on' )
		);
	}

	/**
	 * Test set minify with exception.
	 */
	public function testSetMinifyWithException() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];

		// The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
		$cloudflare_facade_mock->shouldReceive('change_minify')->andThrow( new \Exception() );

		$this->assertEquals(
			new \WP_Error(),
			$cloudflare->set_minify( 'on' )
		);
	}


	/**
	 * Test set minify with no success.
	 */
	public function testSetMinifyWithNoSuccess() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];

		// The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		Functions\when( 'wp_sprintf_l' )->justReturn( '' );
		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
		$cf_reply   = json_decode('{"success":false,"errors":[{"code":1007,"message":"Invalid value for zone setting minify"}],"messages":[],"result":null}');
		$cloudflare_facade_mock->shouldReceive('change_minify')->andReturn( $cf_reply );

		$this->assertEquals(
			new \WP_Error(),
			$cloudflare->set_minify( 'on' )
		);
	}

	/**
	 * Test set minify with success.
	 */
	public function testSetMinifyWithSuccess() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];

		// The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
		$cf_reply = json_decode('{"result":{"id":"minify","value":{"js":"on","css":"on","html":"on"},"modified_on":"","editable":true},"success":true,"errors":[],"messages":[]}');
		$cloudflare_facade_mock->shouldReceive('change_minify')->andReturn( $cf_reply );

		$this->assertEquals(
			'on',
			$cloudflare->set_minify( 'on' )
		);
	}

	/**
	 * Get the mocks required by Cloudflare’s constructor.
	 *
	 * @since  3.5
	 * @author Soponar Cristina
	 * @access private
	 *
	 * @param integer $do_cloudflare      - Value to return for $options->get( 'do_cloudflare' ).
	 * @param string  $cloudflare_email   - Value to return for $options->get( 'cloudflare_email' ).
	 * @param string  $cloudflare_api_key - Value to return for $options->get( 'cloudflare_api_key' ).
	 * @param string  $cloudflare_zone_id - Value to return for $options->get( 'cloudflare_zone_id' ).
	 * @return Mock Options_Data mock
	 */
	private function getConstructorMocks( $do_cloudflare = 1, $cloudflare_email = '',  $cloudflare_api_key = '', $cloudflare_zone_id = '') {
		$options = $this->createMock('WP_Rocket\Admin\Options_Data');
		$map     = [
			[
				'do_cloudflare',
				'',
				$do_cloudflare,
			],
			[
				'cloudflare_email',
				null,
				$cloudflare_email,
			],
			[
				'cloudflare_api_key',
				null,
				$cloudflare_api_key,
			],
			[
				'cloudflare_zone_id',
				null,
				$cloudflare_zone_id,
			],
		];
		$options->method('get')->will( $this->returnValueMap( $map ) );

		$facade   = \Mockery::mock( \WP_Rocket\Addons\Cloudflare\CloudflareFacade::class );
		$wp_error = \Mockery::mock( \WP_Error::class );

		$mocks = [
			'options'  => $options,
			'facade'   => $facade,
			'wp_error' => $wp_error,
		];
		return $mocks;
	}
}
