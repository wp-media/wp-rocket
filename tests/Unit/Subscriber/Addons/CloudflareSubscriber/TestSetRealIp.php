<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Addons\CloudflareSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;
use Brain\Monkey\Functions;

class TestSetRealIp extends TestCase {
	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
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
	 * Test should not set real IP.
	 */
	public function testShouldNotSetIP() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldNotReceive('get_cloudflare_ips');

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$cloudflare_subscriber->set_real_ip();
	}

	/**
	 * Test Should set not set any IP because it not in range.
	 */
	public function testIPNotInRange() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '172.64.0.1';
		$_SERVER['REMOTE_ADDR']           = '172.64.0.15';

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldReceive('get_cloudflare_ips')->andReturn( $mocks['cf_ips'] );

		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'get_rocket_ipv6_full' )->returnArg();
		Functions\when( 'rocket_ipv6_in_range' )->justReturn( false );
		Functions\when( 'rocket_ipv4_in_range' )->justReturn( false );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->set_real_ip();

		$this->assertNotEquals(
			$_SERVER['HTTP_CF_CONNECTING_IP'],
			$_SERVER['REMOTE_ADDR']
		);
	}

	/**
	 * Test Should set real IPv4.
	 */
	public function testShouldSetRealIP4() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '172.64.0.1';
		$_SERVER['REMOTE_ADDR']           = '172.64.0.15';

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldReceive('get_cloudflare_ips')->andReturn( $mocks['cf_ips'] );

		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'get_rocket_ipv6_full' )->returnArg();
		Functions\when( 'rocket_ipv6_in_range' )->justReturn( false );
		Functions\when( 'rocket_ipv4_in_range' )->justReturn( true );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->set_real_ip();

		$this->assertSame(
			$_SERVER['HTTP_CF_CONNECTING_IP'],
			$_SERVER['REMOTE_ADDR']
		);
	}

	/**
	 * Test Should set real IPv6.
	 */
	public function testShouldSetRealIP6() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '2a06:98c0::/29';
		$_SERVER['REMOTE_ADDR']           = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldReceive('get_cloudflare_ips')->andReturn( $mocks['cf_ips'] );

		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'get_rocket_ipv6_full' )->returnArg();
		Functions\when( 'rocket_ipv6_in_range' )->justReturn( true );
		Functions\when( 'rocket_ipv4_in_range' )->justReturn( false );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->set_real_ip();

		$this->assertSame(
			$_SERVER['HTTP_CF_CONNECTING_IP'],
			$_SERVER['REMOTE_ADDR']
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
	 * @return array                      - Array of Mocks
	 */
	private function getConstructorMocks( $do_cloudflare = 1, $cloudflare_email = '',  $cloudflare_api_key = '', $cloudflare_zone_id = '') {
		$options      = $this->createMock('WP_Rocket\Admin\Options');
		$options_data = $this->createMock('WP_Rocket\Admin\Options_Data');
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
		$options_data->method('get')->will( $this->returnValueMap( $map ) );

		$cf_ips = (object) [
			'success' => true,
			'result'  => (object) [],
		];

		$cf_ips->result->ipv4_cidrs = [
			'103.21.244.0/22',
			'103.22.200.0/22',
			'103.31.4.0/22',
			'104.16.0.0/12',
			'108.162.192.0/18',
			'131.0.72.0/22',
			'141.101.64.0/18',
			'162.158.0.0/15',
			'172.64.0.0/13',
			'173.245.48.0/20',
			'188.114.96.0/20',
			'190.93.240.0/20',
			'197.234.240.0/22',
			'198.41.128.0/17',
		];

		$cf_ips->result->ipv6_cidrs = [
			'2400:cb00::/32',
			'2405:8100::/32',
			'2405:b500::/32',
			'2606:4700::/32',
			'2803:f800::/32',
			'2c0f:f248::/32',
			'2a06:98c0::/29',
		];

		$mocks = [
			'options_data' => $options_data,
			'options'      => $options,
			'cf_ips'       => $cf_ips,
		];

		return $mocks;
	}
}
