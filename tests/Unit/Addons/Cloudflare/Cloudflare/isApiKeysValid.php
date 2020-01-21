<?php
namespace WP_Rocket\Tests\Unit\Addons\Cloudflare;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use Brain\Monkey\Functions;

/**
 * @covers WP_Rocket\Addons\Cloudflare\Cloudflare::is_api_keys_valid
 *
 * @group Cloudflare
 */
class Test_IsApiKeysValid extends TestCase {

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
	 * Test Cloudflare API valid keys with empty values.
	 */
	public function testApiKeysWithEmptyValues() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// Set rocket_cloudflare_is_api_keys_valid transient to WP_error for constructor.
		Functions\when( 'get_transient' )->justReturn( $wp_error );

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );

		$this->assertEquals(
			new \WP_Error(),
			$cloudflare->is_api_keys_valid( '', '', '' )
		);
	}

	/**
	 * Test Cloudflare API valid keys with null values.
	 */
	public function testApiKeysWithNullValues() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// Set rocket_cloudflare_is_api_keys_valid transient to WP_error for constructor.
		Functions\when( 'get_transient' )->justReturn( $wp_error );

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );

		$this->assertEquals(
			new \WP_Error(),
			$cloudflare->is_api_keys_valid( null, null, null )
		);
	}

	/**
	 * Test Cloudflare API valid keys with empty zone value.
	 */
	public function testApiKeysWithEmptyZoneValue() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// Set rocket_cloudflare_is_api_keys_valid transient to WP_error for constructor.
		Functions\when( 'get_transient' )->justReturn( $wp_error );

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );

		$this->assertEquals(
			new \WP_Error(),
			$cloudflare->is_api_keys_valid( 'test@test.com', 'API_KEY', '' )
		);
	}

	/**
	 * Test Cloudflare API valid keys with wrong credentials
	 */
	public function testApiKeysWithWrongCredentialsExceptionThrown() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// Set rocket_cloudflare_is_api_keys_valid transient to WP_error for constructor.
		Functions\when( 'get_transient' )->justReturn( $wp_error );

		$cloudflare_facade_mock->shouldReceive('set_api_credentials')->andThrow( new \Exception() );
		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );

		$this->assertEquals(
			new \WP_Error(),
			$cloudflare->is_api_keys_valid( 'test@test.com', 'API_KEY', 'ZONE_ID' )
		);
	}

	/**
	 * Test Cloudflare API valid keys with wrong zone id, correct credentials.
	 */
	public function testApiKeysWithWrongZoneId() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// Set rocket_cloudflare_is_api_keys_valid transient to WP_error for constructor.
		Functions\when( 'get_transient' )->justReturn( $wp_error );

		$cloudflare_facade_mock->shouldReceive('set_api_credentials');
		$zone = json_decode('{"success":false,"errors":[{"code":7003,"message":"Could not route to \/zones\/ZONE_ID, perhaps your object identifier is invalid?"},{"code":7000,"message":"No route for that URI"}],"messages":[],"result":null}');
		$cloudflare_facade_mock->shouldReceive('get_zones')->andReturn( $zone );
		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );

		$this->assertEquals(
			new \WP_Error(),
			$cloudflare->is_api_keys_valid( 'test@test.com', 'API_KEY', 'ZONE_ID' )
		);
	}


	/**
	 * Test Cloudflare API valid keys with wrong domain mapping.
	 */
	public function testApiKeysWithWrongDomainMapping() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// Set rocket_cloudflare_is_api_keys_valid transient to WP_error for constructor.
		Functions\when( 'get_transient' )->justReturn( $wp_error );
		Functions\when( 'get_site_url' )->justReturn( 'another-url.com' );
		Functions\when( 'domain_mapping_siteurl' )->justReturn( 'another-url.com' );
		Functions\when( 'wp_parse_url' )->justReturn( [ 'host' => 'another-url.com' ] );

		$cloudflare_facade_mock->shouldReceive('set_api_credentials');
		$zone = json_decode('{"result":{"id":"","name":"test.com","status":"active","paused":false,"type":"full","development_mode":-1119912,"name_servers":["",""],"original_name_servers":["",""],"original_registrar":null,"original_dnshost":null,"modified_on":"","created_on":"","activated_on":"","meta":{"step":3,"wildcard_proxiable":false,"custom_certificate_quota":0,"page_rule_quota":3,"phishing_detected":false,"multiple_railguns_allowed":false},"owner":{"id":"","type":"user","email":""},"account":{"id":"","name":""},"permissions":["#access:edit","#access:read","#analytics:read","#app:edit","#auditlogs:read","#billing:edit","#billing:read","#cache_purge:edit","#dns_records:edit","#dns_records:read","#lb:edit","#lb:read","#legal:edit","#legal:read","#logs:edit","#logs:read","#member:edit","#member:read","#organization:edit","#organization:read","#ssl:edit","#ssl:read","#stream:edit","#stream:read","#subscription:edit","#subscription:read","#waf:edit","#waf:read","#webhooks:edit","#webhooks:read","#worker:edit","#worker:read","#zone:edit","#zone:read","#zone_settings:edit","#zone_settings:read"],"plan":{"id":"","name":"Free Website","price":0,"currency":"USD","frequency":"","is_subscribed":true,"can_subscribe":false,"legacy_id":"free","legacy_discount":false,"externally_managed":false}},"success":true,"errors":[],"messages":[]}');
		$cloudflare_facade_mock->shouldReceive('get_zones')->andReturn( $zone );
		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );

		$this->assertEquals(
			new \WP_Error(),
			$cloudflare->is_api_keys_valid( 'test@test.com', 'API_KEY', 'ZONE_ID' )
		);
	}

	/**
	 * Test Cloudflare API valid keys.
	 */
	public function testApiKeysValid() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// Set rocket_cloudflare_is_api_keys_valid transient to WP_error for constructor.
		Functions\when( 'get_transient' )->justReturn( $wp_error);

		Functions\when( 'get_site_url' )->justReturn( 'test.com' );
		Functions\when( 'domain_mapping_siteurl' )->justReturn( 'test.com' );
		Functions\when( 'wp_parse_url' )->justReturn( [ 'host' => 'test.com' ] );

		$cloudflare_facade_mock->shouldReceive('set_api_credentials');
		$zone = json_decode('{"result":{"id":"","name":"test.com","status":"active","paused":false,"type":"full","development_mode":-1119912,"name_servers":["",""],"original_name_servers":["",""],"original_registrar":null,"original_dnshost":null,"modified_on":"","created_on":"","activated_on":"","meta":{"step":3,"wildcard_proxiable":false,"custom_certificate_quota":0,"page_rule_quota":3,"phishing_detected":false,"multiple_railguns_allowed":false},"owner":{"id":"","type":"user","email":""},"account":{"id":"","name":""},"permissions":["#access:edit","#access:read","#analytics:read","#app:edit","#auditlogs:read","#billing:edit","#billing:read","#cache_purge:edit","#dns_records:edit","#dns_records:read","#lb:edit","#lb:read","#legal:edit","#legal:read","#logs:edit","#logs:read","#member:edit","#member:read","#organization:edit","#organization:read","#ssl:edit","#ssl:read","#stream:edit","#stream:read","#subscription:edit","#subscription:read","#waf:edit","#waf:read","#webhooks:edit","#webhooks:read","#worker:edit","#worker:read","#zone:edit","#zone:read","#zone_settings:edit","#zone_settings:read"],"plan":{"id":"","name":"Free Website","price":0,"currency":"USD","frequency":"","is_subscribed":true,"can_subscribe":false,"legacy_id":"free","legacy_discount":false,"externally_managed":false}},"success":true,"errors":[],"messages":[]}');
		$cloudflare_facade_mock->shouldReceive('get_zones')->andReturn( $zone );
		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );

		$this->assertEquals(
			true,
			$cloudflare->is_api_keys_valid( 'test@test.com', 'API_KEY', 'ZONE_ID' )
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

		$facade   = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\CloudflareFacade::class);
		$wp_error = \Mockery::mock( \WP_Error::class );

		$mocks = [
			'options'  => $options,
			'facade'   => $facade,
			'wp_error' => $wp_error,
		];
		return $mocks;
	}
}
