<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\Cloudflare\Subscriber;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Tests\Unit\TestCase;
use WPMedia\Cloudflare\Auth\AuthFactoryInterface;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Subscriber::auto_purge_by_url
 *
 * @group Cloudflare
 */
class TestAutoPurgeByUrl extends TestCase {
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
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'current_user_can' )
			->justReturn( $config['cap'] );

		Functions\when( 'is_wp_error' )
			->justReturn( $config['error'] );

		$this->cloudflare->shouldReceive( 'check_connection' )
			->andReturn( true );

		$this->cloudflare->shouldReceive( 'has_page_rule' )
			->with( 'cache_everything' )
			->atMost()
			->once()
			->andReturn( $config['page_rule'] );

		Functions\when( 'get_rocket_i18n_home_url' )
			->justReturn( 'http://example.org' );

		Functions\when( 'get_feed_link' )
			->justReturn( 'http://example.org/feed/' );

		if ( null === $expected ) {
			$this->cloudflare->expects()
				->purge_by_url( '', $config['urls'], '' )
				->never();
		} else {
			$this->cloudflare->expects()
				->purge_by_url( '', $config['urls'], '' )
				->once();
		}

		$this->subscriber->auto_purge_by_url( '', $config['urls'], '' );
	}
}
