<?php
namespace WP_Rocket\Tests\Unit\Preload\Process;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use WP_Rocket\Preload\Full_Process;
use WP_Rocket\Preload\Sitemap;

/**
 * @covers \WP_Rocket\Tests\Unit\Preload\Process\Sitemap::run_preload
 * @group Preload
 */
class Test_runPreload extends TestCase {

	public function testShouldNotPreloadWhenNoUrls() {
		$preload_process = $this->createMock( Full_Process::class );

		Actions\expectDone( 'before_run_rocket_sitemap_preload' )->never();

		// No URLs.
		( new Sitemap( $preload_process ) )->run_preload( [] );

		$this->assertTrue( true );
	}

	public function testShouldPreloadSitemapsWhenValidUrls() {
		$queue    = [];
		$sitemaps = [
			'https://wordpress.org/sitemap.xml',
			'https://wordpress.org/sitemap-mobile.xml',
		];

		// Stubs.
		$preload_process = $this->createMock( Full_Process::class );
		$preload_process
			->expects( $this->any() )
			->method( 'is_mobile_preload_enabled' )
			->willReturn( true );
		$preload_process
			->expects( $this->any() )
			->method( 'push_to_queue' )
			->will( $this->returnCallback( function ( $item ) use ( &$queue ) {
				$queue[] = $item;
			} ) );
		$preload_process
			->expects( $this->once() )
			->method( 'save' )
			->willReturnSelf();
		$preload_process
			->expects( $this->once() )
			->method( 'dispatch' )
			->willReturn( null );

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return \parse_url( $url, $component );
		} );
		Functions\when( 'get_rocket_cache_reject_uri' )->justReturn( '/foo/|/bar/|/(?:.+/)?embed/' );
		Functions\when( 'set_transient' )->justReturn( null );

		// Stubs for $this->process_sitemap().
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'wp_remote_get' )->alias( function( $url, $args = [] ) {
			switch ( $url ) {
				case 'https://wordpress.org/sitemap.xml':
					return [ 'body' => \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Preload/Sitemap/sitemap.xml' ) ];
				case 'https://wordpress.org/sitemap-mobile.xml':
					return [ 'body' => \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Preload/Sitemap/sitemap-mobile.xml' ) ];
			}
			return false;
		} );
		Functions\when( 'get_transient' )->alias( function( $transient ) {
			switch ( $transient ) {
				case 'rocket_preload_errors':
					return [];
			}
			return false;
		} );
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->alias( function ( $response ) {
			if ( ! is_array( $response ) || ! isset( $response['body'] ) ) {
				return '';
			}
			return $response['body'];
		} );
		Functions\when( 'trailingslashit' )->alias( function( $url ) {
			return rtrim( $url, '/' ) . '/';
		} );

		$preload = new Sitemap( $preload_process );

		$preload->run_preload( $sitemaps );

		$this->assertContains( 'https://wordpress.org/', $queue );
		$this->assertContains( 'https://wordpress.org/fr/', $queue );
		$this->assertContains( 'https://wordpress.org/es/', $queue );
		$this->assertContains( [ 'url' => 'https://wordpress.org/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://wordpress.org/fr/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://wordpress.org/es/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://wordpress.org/mobile/de/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://wordpress.org/mobile/fr/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://wordpress.org/mobile/es/', 'mobile' => true ], $queue );
		$this->assertCount( 9, $queue );
	}
}
