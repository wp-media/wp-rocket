<?php
namespace WP_Rocket\Tests\Unit\Addons\Cloudflare;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use Brain\Monkey\Functions;

/**
 * @covers WP_Rocket\Addons\Cloudflare\Cloudflare::get_cloudflare_ips
 *
 * @group Cloudflare
 */
class Test_GetCloudflareIpS extends TestCase {

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
	 * Test get cloudflare IPs with cached invalid transient for credentials.
	 */
	public function testGetCloudflareIPSWithInvalidCredentials() {
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

		Functions\when( 'get_transient' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');
		$cloudflare_facade_mock->shouldReceive('ips')->andThrow( new \Exception() );
		Functions\expect( 'set_transient' )->once();

		$this->assertEquals(
			$mocks[ 'cf_ips' ],
			$cloudflare->get_cloudflare_ips()
		);
	}

	/**
	 * Test get cloudflare IPs with invalid credentials and cached IPs in transient `rocket_cloudflare_ips`.
	 */
	public function testGetCloudflareIPSWithInvalidCredentialsButIPSCached() {
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

		Functions\when( 'get_transient' )->justReturn( $mocks[ 'cf_ips' ] );
		$cloudflare_facade_mock->shouldNotReceive('set_api_credentials');

		$this->assertEquals(
			$mocks[ 'cf_ips' ],
			$cloudflare->get_cloudflare_ips()
		);
	}

	/**
	 * The get Cloudflare IPs with valid CF credentials, no cached `rocket_cloudflare_ips` and error on `ips()`.
	 */
	public function testGetCloudflareIPSWithValidCredentialsAndNoCachedIPSWithError() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		 // The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );

		Functions\when( 'get_transient' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');
		$cf_reply = json_decode('{"success":false,"errors":[{"code":1007,"message":"Invalid value"}],"messages":[],"result":null}');
		$cloudflare_facade_mock->shouldReceive('ips')->andReturn( $cf_reply );
		Functions\expect( 'set_transient' )->once();

		$this->assertEquals(
			$mocks[ 'cf_ips' ],
			$cloudflare->get_cloudflare_ips()
		);
	}

	/**
	 * The get Cloudflare IPs with valid CF credentials, no cached `rocket_cloudflare_ips` and success `ips()`.
	 */
	public function testGetCloudflareIPSWithValidCredentialsAndNoCachedIPSWithSuccess() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		 // The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );

		Functions\when( 'get_transient' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');
		$cf_reply = json_decode('{"result":{"ipv4_cidrs":["173.245.48.0/20","103.21.244.0/22","103.22.200.0/22","103.31.4.0/22","141.101.64.0/18","108.162.192.0/18","190.93.240.0/20","188.114.96.0/20","197.234.240.0/22","198.41.128.0/17","162.158.0.0/15","104.16.0.0/12","172.64.0.0/13","131.0.72.0/22"],"ipv6_cidrs":["2400:cb00::/32","2606:4700::/32","2803:f800::/32","2405:b500::/32","2405:8100::/32","2a06:98c0::/29","2c0f:f248::/32"],"etag":"fb21705459fea38d23b210ee7d67b753"},"success":true,"errors":[],"messages":[]}');
		$cloudflare_facade_mock->shouldReceive('ips')->andReturn( $cf_reply );
		Functions\expect( 'set_transient' )->once();

		$ips = $cloudflare->get_cloudflare_ips();

		$this->assertEquals(
			$mocks[ 'cf_ips' ]->result->ipv4_cidrs,
			$ips->result->ipv4_cidrs
		);
		$this->assertEquals(
			$mocks[ 'cf_ips' ]->result->ipv6_cidrs,
			$ips->result->ipv6_cidrs
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

		$cf_ips = (object) [
			'result'   => (object) [],
			'success'  => true,
			'errors'   => [],
			'messages' => [],
		];

		$cf_ips->result->ipv4_cidrs = [
			'173.245.48.0/20',
			'103.21.244.0/22',
			'103.22.200.0/22',
			'103.31.4.0/22',
			'141.101.64.0/18',
			'108.162.192.0/18',
			'190.93.240.0/20',
			'188.114.96.0/20',
			'197.234.240.0/22',
			'198.41.128.0/17',
			'162.158.0.0/15',
			'104.16.0.0/12',
			'172.64.0.0/13',
			'131.0.72.0/22',
		];

		$cf_ips->result->ipv6_cidrs = [
			'2400:cb00::/32',
			'2606:4700::/32',
			'2803:f800::/32',
			'2405:b500::/32',
			'2405:8100::/32',
			'2a06:98c0::/29',
			'2c0f:f248::/32',
		];

		$mocks = [
			'options'  => $options,
			'facade'   => $facade,
			'wp_error' => $wp_error,
			'cf_ips'   => $cf_ips,
		];
		return $mocks;
	}
}
