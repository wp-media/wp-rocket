<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\ServiceProvider;

use Brain\Monkey\Functions;
use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Kinsta;
use WP_Rocket\ThirdParty\Hostings\Nginx;
use WP_Rocket\ThirdParty\Hostings\ServiceProvider;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\ServiceProvider::register / ::boot
 *
 * Nginx is intentionally registered independently of HostResolver (see issue #8768): it must be
 * bound alongside a HostResolver-matched host in the same request, proving the "webserver +
 * hosting provider co-occurrence" scenario is supported by design, not a regression.
 *
 * @group ThirdParty
 * @group Hostings
 */
class Test_RegisterAndBoot extends TestCase {
	protected function tearDown(): void {
		unset( $_SERVER['KINSTA_CACHE_ZONE'] );

		global $is_nginx;
		$is_nginx = null;

		parent::tearDown();
	}

	/**
	 * Mocks the WordPress functions that HostResolver::get_host_service() calls while walking through
	 * the branches preceding the one under test in a given scenario (onecom/cloudways sanitization,
	 * siteground's rocket_is_plugin_active()).
	 */
	private function mock_host_resolver_dependencies() {
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'get_option' )->justReturn( [] );
		Functions\when( 'is_multisite' )->justReturn( false );
	}

	/**
	 * HostResolver::get_host_service() caches its result in a static property for the lifetime of the
	 * process. Each scenario below runs in its own process so the cache starts fresh every time.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldBindOnlyHostResolverServiceWhenNginxNotActive() {
		$this->mock_host_resolver_dependencies();

		$_SERVER['KINSTA_CACHE_ZONE'] = true;

		global $is_nginx;
		$is_nginx = false;

		$container = new Container();
		$provider  = new ServiceProvider();
		$provider->setContainer( $container );

		$provider->register();
		$provider->boot();

		$this->assertTrue( $provider->provides( 'kinsta' ) );
		$this->assertFalse( $provider->provides( 'nginx' ) );
		$this->assertInstanceOf( Kinsta::class, $container->get( 'kinsta' ) );
		$this->assertFalse( $container->has( 'nginx' ) );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldBindBothHostResolverServiceAndNginxWhenBothActive() {
		$this->mock_host_resolver_dependencies();

		$_SERVER['KINSTA_CACHE_ZONE'] = true;

		global $is_nginx;
		$is_nginx = true;

		$container = new Container();
		$provider  = new ServiceProvider();
		$provider->setContainer( $container );

		$provider->register();
		$provider->boot();

		$this->assertTrue( $provider->provides( 'kinsta' ) );
		$this->assertTrue( $provider->provides( 'nginx' ) );
		$this->assertInstanceOf( Kinsta::class, $container->get( 'kinsta' ) );
		$this->assertInstanceOf( Nginx::class, $container->get( 'nginx' ) );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldBindOnlyNginxWhenNoHostDetected() {
		$this->mock_host_resolver_dependencies();

		global $is_nginx;
		$is_nginx = true;

		$container = new Container();
		$provider  = new ServiceProvider();
		$provider->setContainer( $container );

		$provider->register();
		$provider->boot();

		$this->assertFalse( $container->has( 'kinsta' ) );
		$this->assertInstanceOf( Nginx::class, $container->get( 'nginx' ) );
	}
}
