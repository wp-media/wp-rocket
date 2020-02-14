<?php
namespace WP_Rocket\Tests\Unit\Preload\Sitemap;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

use WP_Rocket\Preload\Full_Process;
use WP_Rocket\Preload\Sitemap;

/**
 * @covers \WP_Rocket\Preload\Sitemap::run_preload
 * @group Preload
 */
class Test_runPreload extends TestCase {

	public function testShouldNotPreloadWhenNoUrls() {
		$preload_process = $this->createMock( Full_Process::class );

		Actions\expectDone( 'before_run_rocket_sitemap_preload' )->never();

		// No URLs.
		( new Sitemap( $preload_process ) )->run_preload( [] );
	}

	public function testShouldPreloadSitemapsWhenValidUrls() {
		$queue    = [];
		$sitemaps = [
			'https://example.com/sitemap.xml',
			'https://example.com/sitemap-mobile.xml',
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
				case 'https://example.com/sitemap.xml':
					return [ 'body' => \file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Preload/Sitemap/sitemap.xml' ) ];
				case 'https://example.com/sitemap-mobile.xml':
					return [ 'body' => \file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Preload/Sitemap/sitemap-mobile.xml' ) ];
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

		// Stubs for $this->get_url_identifier().
		Functions\when( 'get_rocket_cache_query_string' )->justReturn( [] );
		Functions\when( 'trailingslashit' )->alias( function( $url ) {
			return rtrim( $url, '/' ) . '/';
		} );

		$preload = new Sitemap( $preload_process );

		$preload->run_preload( $sitemaps );

		$this->assertContains( 'https://example.com/', $queue );
		$this->assertContains( 'https://example.com/fr/', $queue );
		$this->assertContains( 'https://example.com/es/', $queue );
		$this->assertContains( [ 'url' => 'https://example.com/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/fr/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/es/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/mobile/de/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/mobile/fr/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/mobile/es/', 'mobile' => true ], $queue );
		$this->assertCount( 9, $queue );
	}
}
