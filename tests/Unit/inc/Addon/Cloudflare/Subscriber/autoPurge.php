<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\Cloudflare\Subscriber;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Subscriber::auto_purge
 *
 * @group Cloudflare
 */
class TestAutoPurge extends TestCase {
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
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'current_user_can' )
			->justReturn( $config['cap'] );

		Functions\when( 'is_wp_error' )
			->justReturn( $config['error'] );

		$this->cloudflare->expects()
			->has_page_rule( 'cache_everything' )
			->atMost()
			->once()
			->andReturn( $config['page_rule'] );

		if ( null === $expected ) {
			$this->cloudflare->expects()
				->purge_cloudflare()
				->never();
		} else {
			$this->cloudflare->expects()
				->purge_cloudflare()
				->once();
		}

		$this->subscriber->auto_purge();
	}
}
