<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Subscriber;

use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Addon\Cloudflare\Subscriber;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Tests\Unit\TestCase;
use WPMedia\Cloudflare\Auth\AuthFactoryInterface;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Subscriber::set_varnish_localhost
 *
 * @group Cloudflare
 */
class TestSetVarnishLocalhost extends TestCase {
	private $options_api;
	private $options;
	private $cloudflare;

	private $factory;
	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->options_api = Mockery::mock( Options::class );
		$this->options     = Mockery::mock( Options_Data::class );
		$this->cloudflare  = Mockery::mock( Cloudflare::class );
		$this->factory = Mockery::mock( AuthFactoryInterface::class );
		$this->subscriber  = new Subscriber( $this->cloudflare, $this->options, $this->options_api, $this->factory );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'varnish_auto_purge', 0 )
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
