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

	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Fixture config; is_plugin_active()'s active flag and CF credentials.
	 * @param array $expected Fixture expectation; 'should_unregister' is whether unregister_callback() must run.
	 */
	public function testShouldUnregisterOnlyWhenPluginActive( $config, $expected ) {
		Functions\expect( 'is_plugin_active' )
			->with( 'cloudflare/cloudflare.php' )
			->once()
			->andReturn( $config['plugin_active'] );

		Functions\when( 'get_option' )->alias(
			function ( $option_name, $default = false ) use ( $config ) {
				switch ( $option_name ) {
					case 'cloudflare_api_email':
						return $config['cloudflare_api_email'];
					case 'cloudflare_api_key':
						return $config['cloudflare_api_key'];
					case 'cloudflare_cached_domain_name':
						return $config['cloudflare_cached_domain_name'];
					default:
						return $default;
				}
			}
		);

		$cloudflare = Mockery::mock(
			Cloudflare::class,
			[ $this->options, $this->option_api, $this->beacon, $this->facade ]
		)->makePartial()->shouldAllowMockingProtectedMethods();

		if ( $expected['should_unregister'] ) {
			$cloudflare->shouldReceive( 'unregister_callback' )
				->once()
				->with( 'deleted_post', 'purgeCacheByRelevantURLs' );

			$cloudflare->shouldReceive( 'unregister_callback' )
				->once()
				->with( 'transition_post_status', 'purgeCacheOnPostStatusChange', PHP_INT_MAX );
		} else {
			$cloudflare->shouldNotReceive( 'unregister_callback' );
		}

		$cloudflare->unregister_cloudflare_clean_on_post();
	}
}
