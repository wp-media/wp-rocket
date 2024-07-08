<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\PreloadUrl;

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\{PreloadUrl, Queue};
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Controller\PreloadUrl::preload_url
 *
 * @group Preload
 */
class Test_PreloadUrl extends TestCase {
	protected $queue;
	protected $query;
	protected $options;
	protected $controller;
	protected $file_system;

	protected function setUp(): void {
		parent::setUp();

		$this->options     = Mockery::mock( Options_Data::class );
		$this->query       = $this->createMock( Cache::class );
		$this->queue       = Mockery::mock( Queue::class );
		$this->file_system = Mockery::mock( WP_Filesystem_Direct::class );
		$this->controller  = Mockery::mock( PreloadUrl::class . '[get_mobile_user_agent_prefix,is_already_cached]',
			[
				$this->options,
				$this->queue,
				$this->query,
				$this->file_system,
			]
		)->shouldAllowMockingProtectedMethods();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config ) {

		Functions\expect( 'get_transient' )
			->atMost()
			->once()
			->with( 'rocket_preload_check_duration' )
			->andReturn( $config['transient_check_duration'] );

		$this->options->expects()->get( 'do_caching_mobile_files', false )
			->andReturn( $config['cache_mobile'] );
		$this->controller->expects()->is_already_cached( $config['url'] )
			->andReturn( $config['cache_exists'] );

		$this->expectDesktopRequest( $config );
		$this->expectMobileRequest( $config );

		if ( false === $config['transient_check_duration'] ) {
			Functions\expect( 'get_transient' )
				->atMost()
				->once()
				->with( 'rocket_preload_previous_requests_durations' )
				->andReturn( false );

			Functions\expect( 'set_transient' )
				->atMost()
				->once()
				->with( 'rocket_preload_previous_requests_durations', Mockery::type( 'int' ), 300 );

			Functions\expect( 'set_transient' )
				->atMost()
				->once()
				->with( 'rocket_preload_check_duration', Mockery::type( 'int' ), 60 );
		}

		$this->controller->preload_url( $config['url'] );
	}

	protected function expectDesktopRequest( $config ) {
		if ( $config['cache_exists'] ) {
			$delay_value = 500000;

			if ( isset( $config['rocket_preload_delay_between_requests'] ) ) {
				$delay_value = $config['rocket_preload_delay_between_requests'];
			}

			Functions\expect('apply_filters')->with( 'rocket_preload_delay_between_requests', 500000 )->andReturn( $delay_value );

			Functions\expect( 'wp_safe_remote_get' )
			->with( $config['url'] . '/', $config['request']['config'] )
			->never();

			return;
		}

		Functions\expect( 'wp_safe_remote_get' )
			->with( $config['url'] . '/', $config['request']['config'] );
	}

	protected function expectMobileRequest( $config ) {
		if (
			$config['cache_exists']
			||
			! $config['cache_mobile']
		) {
			return;
		}

		$this->controller->expects()->get_mobile_user_agent_prefix()
			->andReturn( $config['user_agent'] );

		Functions\expect( 'wp_safe_remote_get' )
			->with( $config['url'] . '/', $config['request_mobile']['config'] );
	}
}
