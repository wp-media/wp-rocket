<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\ThirdParty\Plugins\CDN\{Cloudflare,CloudflareFacade};
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::unregister_cloudflare_clean_on_post
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_unregisterCloudflareCleanOnPost extends TestCase {

	/**
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * @var Beacon
	 */
	protected $beacon;

	/**
	 * @var Options
	 */
	protected $option_api;

	/**
	 * @var CloudflareFacade|\Mockery\MockInterface
	 */
	protected $facade;

	public function set_up() {
		parent::set_up();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->option_api = Mockery::mock( Options::class );
		$this->beacon      = Mockery::mock( Beacon::class );
		$this->facade      = Mockery::mock( CloudflareFacade::class );
	}

	public function testShouldNotUnregisterWhenPluginInactive() {
		Functions\expect( 'is_plugin_active' )
			->with( 'cloudflare/cloudflare.php' )
			->once()
			->andReturn( false );

		$cloudflare = Mockery::mock(
			Cloudflare::class,
			[ $this->options, $this->option_api, $this->beacon, $this->facade ]
		)->makePartial()->shouldAllowMockingProtectedMethods();

		$cloudflare->shouldNotReceive( 'unregister_callback' );

		$cloudflare->unregister_cloudflare_clean_on_post();
	}

	public function testShouldUnregisterWhenPluginActive() {
		Functions\expect( 'is_plugin_active' )
			->with( 'cloudflare/cloudflare.php' )
			->once()
			->andReturn( true );

		Functions\when( 'get_option' )->alias(
			function ( $option_name, $default = false ) {
				switch ( $option_name ) {
					case 'cloudflare_api_email':
						return 'test@example.com';
					case 'cloudflare_api_key':
						return 'test-api-key';
					case 'cloudflare_cached_domain_name':
						return 'example.com';
					default:
						return $default;
				}
			}
		);

		$cloudflare = Mockery::mock(
			Cloudflare::class,
			[ $this->options, $this->option_api, $this->beacon, $this->facade ]
		)->makePartial()->shouldAllowMockingProtectedMethods();

		$cloudflare->shouldReceive( 'unregister_callback' )
			->once()
			->with( 'deleted_post', 'purgeCacheByRelevantURLs' );

		$cloudflare->shouldReceive( 'unregister_callback' )
			->once()
			->with( 'transition_post_status', 'purgeCacheOnPostStatusChange', PHP_INT_MAX );

		$cloudflare->unregister_cloudflare_clean_on_post();
	}
}
