<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Subscriber;

use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Addon\Cloudflare\Subscriber;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Subscriber::protocol_rewrite
 *
 * @group Cloudflare
 */
class TestProtocolRewrite extends TestCase {
	private $options_api;
	private $options;
	private $cloudflare;
	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->options_api = Mockery::mock( Options::class );
		$this->options     = Mockery::mock( Options_Data::class );
		$this->cloudflare  = Mockery::mock( Cloudflare::class );
		$this->subscriber  = new Subscriber( $this->cloudflare, $this->options, $this->options_api );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $value, $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'do_cloudflare', 0 )
			->once()
			->andReturn( $config['cloudflare'] );

		$this->options->shouldReceive( 'get' )
			->with( 'cloudflare_protocol_rewrite', 0 )
			->atMost()
			->once()
			->andReturn( $config['rewrite'] );

		Filters\expectApplied( 'do_rocket_protocol_rewrite' )
			->andReturn( $config['filter'] );

		$this->assertSame(
			$expected,
			$this->subscriber->protocol_rewrite( $value )
		);
	}
}
