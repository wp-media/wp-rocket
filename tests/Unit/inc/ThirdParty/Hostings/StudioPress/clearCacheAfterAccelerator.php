<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\StudioPress;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\StudioPress;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\StudioPress::clear_cache_after_accelerator
 *
 * @group  StudioPress
 * @group  ThirdParty
 */
class Test_ClearCacheAfterAccelerator extends TestCase {

	/**
	 * @var StudioPress
	 */
	protected $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->subscriber = new StudioPress();

		Functions\when( 'wp_unslash' )->returnArg( 1 );
		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
	}

	protected function tearDown(): void {
		unset( $GLOBALS['sp_accel_nginx_proxy_cache_purge'], $_REQUEST['_wpnonce'], $_REQUEST['cache-purge-url'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config ) {
		Functions\expect( 'current_user_can' )
			->with( 'rocket_manage_options' )
			->once()
			->andReturn( $config['has_cap'] );

		if ( ! $config['has_cap'] ) {
			Functions\expect( 'wp_verify_nonce' )->never();
			Functions\expect( 'rocket_clean_files' )->never();
			Functions\expect( 'rocket_clean_domain' )->never();
			Functions\expect( 'run_rocket_bot' )->never();
			Functions\expect( 'run_rocket_sitemap_preload' )->never();

			$this->subscriber->clear_cache_after_accelerator();
			return;
		}

		if ( $config['global_set'] ) {
			$GLOBALS['sp_accel_nginx_proxy_cache_purge'] = Mockery::mock( 'SP_Accel_Nginx_Proxy_Cache_Purge' );
		}

		if ( $config['nonce_present'] ) {
			$_REQUEST['_wpnonce'] = 'test-nonce';
		}

		if ( null !== $config['cache_purge_url'] ) {
			$_REQUEST['cache-purge-url'] = $config['cache_purge_url'];
		}

		if ( $config['global_set'] && $config['nonce_present'] ) {
			Functions\expect( 'wp_verify_nonce' )
				->andReturnUsing(
					function ( $nonce, $action ) use ( $config ) {
						return $action === $config['valid_nonce_action'];
					}
				);
		} else {
			Functions\expect( 'wp_verify_nonce' )->never();
		}

		switch ( $config['expect'] ) {
			case 'clean_files':
				Functions\expect( 'rocket_clean_files' )->once()->with( [ $config['cache_purge_url'] ] );
				Functions\expect( 'rocket_clean_domain' )->never();
				Functions\expect( 'run_rocket_bot' )->never();
				Functions\expect( 'run_rocket_sitemap_preload' )->never();
				break;

			case 'clean_domain':
				Functions\expect( 'rocket_clean_files' )->never();
				Functions\expect( 'rocket_clean_domain' )->once();
				Functions\expect( 'run_rocket_bot' )->once();
				Functions\expect( 'run_rocket_sitemap_preload' )->once();
				break;

			default:
				Functions\expect( 'rocket_clean_files' )->never();
				Functions\expect( 'rocket_clean_domain' )->never();
				Functions\expect( 'run_rocket_bot' )->never();
				Functions\expect( 'run_rocket_sitemap_preload' )->never();
				break;
		}

		$this->subscriber->clear_cache_after_accelerator();
	}
}
