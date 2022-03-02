<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\PreloadSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status\Checker;
use WP_Rocket\Engine\Preload\Homepage;
use WP_Rocket\Engine\Preload\PreloadSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\PreloadSubscriber::preload_homepage
 *
 * @group  Preload
 */
class Test_PreloadHomepage extends TestCase {
	private $subscriber;
	private $homepage_preloader;
	private $home_url   = 'https://example.org/';
	private $user_agent = 'WP Rocket/Homepage Preload';
	private $prefix     = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

	protected function set_up() {
		parent::set_up();

		$this->homepage_preloader = Mockery::mock( Homepage::class );
		$this->subscriber         = new PreloadSubscriber(
			$this->homepage_preloader,
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Checker::class )
		);

		Functions\when( 'home_url' )->justReturn( $this->home_url );
	}

	public function testShouldPreloadDesktopHomepageOnly() {
		Functions\expect( 'wp_safe_remote_get' )
			->once()
			->with(
				$this->home_url,
				[
					'timeout'    => 0.01,
					'blocking'   => false,
					'user-agent' => $this->user_agent,
					'sslverify'  => false,
				]
			);

		$this->subscriber->preload_homepage( [] );
	}

	public function testShouldPreloadDesktopAndMobileHomepage() {
		$this->homepage_preloader
			->shouldReceive( 'get_mobile_user_agent_prefix' )
			->once()
			->andReturn( $this->prefix );

		Functions\expect( 'wp_safe_remote_get' )
			->once()
			->with(
				$this->home_url,
				[
					'timeout'    => 0.01,
					'blocking'   => false,
					'user-agent' => $this->user_agent,
					'sslverify'  => false,
				]
			);

		Functions\expect( 'wp_safe_remote_get' )
			->once()
			->with(
				$this->home_url,
				[
					'timeout'    => 0.01,
					'blocking'   => false,
					'user-agent' => $this->prefix . ' ' . $this->user_agent,
					'sslverify'  => false,
				]
			);

		$this->subscriber->preload_homepage(
			[
				'do_caching_mobile_files' => 1,
			]
		);
	}
}
