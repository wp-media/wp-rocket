<?php
namespace WP_Rocket\Tests\Unit\Addons\Cloudflare;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use Brain\Monkey\Functions;

/**
 * @covers WP_Rocket\Addons\Cloudflare\Cloudflare::get_settings
 *
 * @group Cloudflare
 */
class Test_GetSettings extends TestCase {

	protected function setUp() {
		parent::setUp();

		$this->mockCommonWpFunctions();

		if ( ! defined('WEEK_IN_SECONDS') ) {
			define('WEEK_IN_SECONDS', 7 * 24 * 60 * 60);
		}

		if ( ! defined('WP_ROCKET_VERSION') ) {
			define('WP_ROCKET_VERSION', '3.5');
		}
	}

	/**
	 * Test get settings with cached invalid transient.
	 */
	public function testGetSettingsWithInvalidCredentials() {
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
			$cloudflare->get_settings()
		);
	}

	/**
	 * Test get settings with exception.
	 */
	public function testGetSettingsWithException() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];

		// The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
		$cloudflare_facade_mock->shouldReceive('change_development_mode')->andThrow( new \Exception() );

		$this->assertEquals(
			new \WP_Error(),
			$cloudflare->get_settings()
		);
	}

	/**
	 * Test get settings with no success.
	 */
	public function testGetSettingsWithNoSuccess() {
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
		$cloudflare_facade_mock->shouldReceive('settings')->andReturn( $cf_reply );

		$this->assertEquals(
			new \WP_Error(),
			$cloudflare->get_settings()
		);
	}

	/**
	 * Test get settings with success.
	 */
	public function testGetSettingsWithSuccess() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];

		// The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
		$cf_reply = json_decode('{"result":[{"id":"0rtt","value":"off","modified_on":null,"editable":true},{"id":"advanced_ddos","value":"on","modified_on":null,"editable":false},{"id":"always_online","value":"on","modified_on":"","editable":true},{"id":"always_use_https","value":"off","modified_on":null,"editable":true},{"id":"automatic_https_rewrites","value":"on","modified_on":"","editable":true},{"id":"brotli","value":"on","modified_on":null,"editable":true},{"id":"browser_cache_ttl","value":31536000,"modified_on":"","editable":true},{"id":"browser_check","value":"on","modified_on":null,"editable":true},{"id":"cache_level","value":"aggressive","modified_on":"","editable":true},{"id":"challenge_ttl","value":1800,"modified_on":null,"editable":true},{"id":"ciphers","value":[],"modified_on":null,"editable":true},{"id":"cname_flattening","value":"flatten_at_root","modified_on":null,"editable":false},{"id":"development_mode","value":"off","modified_on":"","time_remaining":0,"editable":true},{"id":"edge_cache_ttl","value":7200,"modified_on":null,"editable":true},{"id":"email_obfuscation","value":"on","modified_on":"","editable":true},{"id":"hotlink_protection","modified_on":"","value":"off","editable":true},{"id":"http2","value":"on","modified_on":null,"editable":false},{"id":"http3","value":"off","modified_on":null,"editable":true},{"id":"ip_geolocation","value":"on","modified_on":"","editable":true},{"id":"ipv6","value":"off","modified_on":"","editable":true},{"id":"max_upload","value":100,"modified_on":null,"editable":true},{"id":"min_tls_version","value":"1.0","modified_on":null,"editable":true},{"id":"minify","value":{"js":"on","css":"on","html":"on"},"modified_on":"","editable":true},{"id":"mirage","value":"off","modified_on":null,"editable":false},{"id":"mobile_redirect","value":{"status":"off","mobile_subdomain":null,"strip_uri":false},"modified_on":null,"editable":true},{"id":"opportunistic_encryption","value":"on","modified_on":null,"editable":true},{"id":"opportunistic_onion","value":"on","modified_on":null,"editable":true},{"id":"origin_error_page_pass_thru","value":"off","modified_on":null,"editable":false},{"id":"polish","value":"off","modified_on":null,"editable":false},{"id":"prefetch_preload","value":"off","modified_on":null,"editable":false},{"id":"privacy_pass","value":"on","modified_on":null,"editable":true},{"id":"pseudo_ipv4","value":"off","modified_on":null,"editable":true},{"id":"response_buffering","value":"off","modified_on":null,"editable":false},{"id":"rocket_loader","value":"off","modified_on":"","editable":true},{"id":"security_header","modified_on":null,"value":{"strict_transport_security":{"enabled":false,"max_age":0,"include_subdomains":false,"preload":false,"nosniff":false}},"editable":true},{"id":"security_level","value":"medium","modified_on":"","editable":true},{"id":"server_side_exclude","value":"on","modified_on":"","editable":true},{"id":"sort_query_string_for_cache","value":"off","modified_on":null,"editable":false},{"id":"ssl","value":"flexible","modified_on":"","certificate_status":"active","validation_errors":[],"editable":true},{"id":"tls_1_2_only","value":"off","modified_on":null,"editable":true},{"id":"tls_1_3","value":"on","modified_on":null,"editable":true},{"id":"tls_client_auth","value":"off","modified_on":null,"editable":true},{"id":"true_client_ip_header","value":"off","modified_on":null,"editable":false},{"id":"waf","value":"off","modified_on":null,"editable":false},{"id":"webp","value":"off","modified_on":null,"editable":false},{"id":"websockets","value":"on","modified_on":"","editable":true}],"success":true,"errors":[],"messages":[]}');
		$cloudflare_facade_mock->shouldReceive('settings')->andReturn( $cf_reply );

		$cf_settings_array = [
			'cache_level'       => 'aggressive',
			'minify'            => 'on',
			'rocket_loader'     => 'off',
			'browser_cache_ttl' => 31536000,
		];
		$this->assertEquals(
			$cf_settings_array,
			$cloudflare->get_settings()
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
