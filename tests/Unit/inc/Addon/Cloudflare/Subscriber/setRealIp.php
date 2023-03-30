<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\Cloudflare\Subscriber;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Subscriber::set_real_ip
 *
 * @group Cloudflare
 */
class TestSetRealIp extends TestCase {
	private $options_api;
	private $options;
	private $cloudflare;
	private $subscriber;

	protected function setUp(): void {
		$this->stubTranslationFunctions();

		$this->options_api = Mockery::mock( Options::class );
		$this->options     = Mockery::mock( Options_Data::class );
		$this->cloudflare  = Mockery::mock( Cloudflare::class );
		$this->subscriber  = new Subscriber( $this->cloudflare, $this->options, $this->options_api );
	}

	protected function tearDown(): void {
		unset( $_SERVER['HTTP_CF_CONNECTING_IP'] );
		unset( $_SERVER['REMOTE_ADDR'] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		if ( null === $expected ) {
			$this->cloudflare->expects()
			->get_cloudflare_ips()
			->never();
		} else {
			$_SERVER['REMOTE_ADDR'] = $config['remote_addr'];
			$_SERVER['HTTP_CF_CONNECTING_IP'] = $config['connecting_ip'];

			$this->cloudflare->expects()
			->get_cloudflare_ips()
			->atMost()
			->once()
			->andReturn( $config['result'] );

			Functions\when( 'sanitize_text_field' )
				->returnArg();

			Functions\when( 'wp_unslash' )
				->returnArg();

			Functions\when( 'get_rocket_ipv6_full' )
				->returnArg();

			Functions\when( 'rocket_ipv4_in_range' )
				->justReturn( $config['in_range'] );

			$this->subscriber->set_real_ip();

			$this->assertSame(
				$expected,
				$_SERVER['REMOTE_ADDR']
			);
		}
	}
}
