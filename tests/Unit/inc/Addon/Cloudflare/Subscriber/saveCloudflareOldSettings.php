<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\Cloudflare\Subscriber;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Subscriber::save_cloudflare_old_settings
 *
 * @group Cloudflare
 */
class TestSaveCloudflareOldSettings extends TestCase {
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
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\when( 'current_user_can' )
			->justReturn( $config['cap'] );

			Functions\expect( 'is_wp_error' )
			->atMost()
			->once()
			->andReturn( false )
			->andAlsoExpectIt()
			->andReturn( $config['error'] );

		$this->cloudflare->shouldReceive( 'check_connection' )
			->andReturn( true );

		$this->cloudflare->shouldReceive( 'get_settings' )
			->atMost()
			->once()
			->andReturn( $config['result'] );

		$this->assertSame(
			$expected,
			$this->subscriber->save_cloudflare_old_settings( $config['value'], $config['old_value'] )
		);
	}
}
