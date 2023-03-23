<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Subscriber;

use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Addon\Cloudflare\Subscriber;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Subscriber::set_varnish_localhost
 *
 * @group Cloudflare
 */
class TestGetCloudflareIps extends TestCase {
	private $options_api;
	private $options;
	private $cloudflare;
	private $subscriber;

	protected function setUp(): void {
		$this->options_api = Mockery::mock( Options::class );
		$this->options     = Mockery::mock( Options_Data::class );
		$this->cloudflare  = Mockery::mock( Cloudflare::class );
		$this->subscriber  = new Subscriber( $this->cloudflare, $this->options, $this->options_api );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->options->expects()
			->get( 'varnish_auto_purge', 0 )
			->atMost()
			->once()
			->andReturn( $config['option'] );

		Filters\expectApplied( 'do_rocket_varnish_http_purge' )
			->atMost()
			->once()
			->andReturn( $config['filter'] );

		$this->assertSame(
			$expected,
			$this->subscriber->set_varnish_localhost( $config['value'] )
		);
	}
}
