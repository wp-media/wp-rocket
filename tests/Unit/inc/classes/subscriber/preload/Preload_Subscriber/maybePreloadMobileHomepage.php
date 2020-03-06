<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\preload\Preload_Subscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Preload\Full_Process;
use WP_Rocket\Preload\Homepage;
use WP_Rocket\Preload\Sitemap;
use WP_Rocket\Subscriber\Preload\Preload_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Preload::maybe_preload_mobile_homepage
 * @group  Preload
 */
class Test_MaybePreloadMobileHomepage extends TestCase {
	private $home_url   = 'https://example.org/';
	private $user_agent = 'WP Rocket/Homepage_Preload_After_Purge_Cache';
	private $prefix     = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

	public function testShouldUseMobilePrefixWhenMobilePreloadIsEnabled() {
		$options            = $this->createMock( Options_Data::class );
		$sitemap_preloader  = $this->createMock( Sitemap::class );
		$homepage_preloader = $this->createMock( Homepage::class );
		$homepage_preloader
			->expects( $this->once() )
			->method( 'is_mobile_preload_enabled' )
			->willReturn( true );
		$homepage_preloader
			->expects( $this->once() )
			->method( 'get_mobile_user_agent_prefix' )
			->willReturn( $this->prefix );

		Functions\expect( 'wp_safe_remote_get' )
			->once()
			->with(
				$this->home_url,
				[
					'user-agent' => $this->prefix . ' ' . $this->user_agent,
				]
			);

		$subscriber = new Preload_Subscriber( $homepage_preloader, $sitemap_preloader, $options );

		$subscriber->maybe_preload_mobile_homepage( $this->home_url, 'whatever', [] );
	}

	public function testShouldNotUseMobilePrefixWhenMobilePreloadIsNotEnabled() {
		$options            = $this->createMock( Options_Data::class );
		$sitemap_preloader  = $this->createMock( Sitemap::class );
		$homepage_preloader = $this->createMock( Homepage::class );
		$homepage_preloader
			->expects( $this->once() )
			->method( 'is_mobile_preload_enabled' )
			->willReturn( false );
		$homepage_preloader
			->expects( $this->never() )
			->method( 'get_mobile_user_agent_prefix' );

		Functions\expect( 'wp_safe_remote_get' )
			->never();

		$subscriber = new Preload_Subscriber( $homepage_preloader, $sitemap_preloader, $options );

		$subscriber->maybe_preload_mobile_homepage( $this->home_url, 'whatever', [] );
	}
}
