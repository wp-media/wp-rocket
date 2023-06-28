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
 * @covers WP_Rocket\Addon\Cloudflare\Subscriber::auto_purge
 *
 * @group Cloudflare
 */
class TestAutoPurge extends TestCase {
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

		$this->configure_reload_options($config, $expected);

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


	protected function configure_reload_options($config, $expected) {
		if(! $config['cap']) {
			return;
		}


		$this->options_api->expects()->get('settings', [])->andReturn($config['settings']);

		$this->options->expects()->set_values($config['settings']);

		$this->factory->expects()->create($config['settings'])->andReturn($config['auth']);

		$this->cloudflare->expects()->change_auth($config['auth']);

		$this->options->expects()->get('cloudflare_zone_id', '')->andReturn($config['cloudflare_zone_id']);
	}
}
