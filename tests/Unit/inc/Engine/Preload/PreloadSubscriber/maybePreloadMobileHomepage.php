<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\PreloadSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status\Checker;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\FullProcess;
use WP_Rocket\Engine\Preload\Homepage;
use WP_Rocket\Engine\Preload\PreloadSubscriber;

/**
 * @covers \WP_Rocket\Engine\Preload\PreloadSubscriber::maybe_preload_mobile_homepage
 * @group  Preload
 */
class Test_MaybePreloadMobileHomepage extends TestCase {
	private $home_url   = 'https://example.org/';
	private $user_agent = 'WP Rocket/Homepage_Preload_After_Purge_Cache';
	private $prefix     = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

	public function testShouldUseMobilePrefixWhenMobilePreloadIsEnabled() {
		$options            = Mockery::mock( Options_Data::class );
		$homepage_preloader = Mockery::mock( Homepage::class );
		$checker            = Mockery::mock( Checker::class );
		$homepage_preloader
			->shouldReceive( 'is_mobile_preload_enabled' )
			->once()
			->andReturn( true );
		$homepage_preloader
			->shouldReceive( 'get_mobile_user_agent_prefix' )
			->once()
			->andReturn( $this->prefix );

		Functions\expect( 'wp_safe_remote_get' )
			->once()
			->with(
				$this->home_url,
				[
					'user-agent' => $this->prefix . ' ' . $this->user_agent,
				]
			);

		$subscriber = new PreloadSubscriber( $homepage_preloader, $options, $checker );

		$subscriber->maybe_preload_mobile_homepage( $this->home_url, 'whatever', [] );
	}

	public function testShouldNotUseMobilePrefixWhenMobilePreloadIsNotEnabled() {
		$options            = Mockery::mock( Options_Data::class );
		$homepage_preloader = Mockery::mock( Homepage::class );
		$checker            = Mockery::mock( Checker::class );
		$homepage_preloader
			->shouldReceive( 'is_mobile_preload_enabled' )
			->once()
			->andReturn( false );
		$homepage_preloader
			->shouldReceive( 'get_mobile_user_agent_prefix' )
			->never();

		Functions\expect( 'wp_safe_remote_get' )
			->never();

		$subscriber = new PreloadSubscriber( $homepage_preloader, $options, $checker );

		$subscriber->maybe_preload_mobile_homepage( $this->home_url, 'whatever', [] );
	}
}
