<?php
namespace WP_Rocket\Tests\Unit\Preload\Process;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;

use WP_Rocket\Preload\Full_Process;
use WP_Rocket\Preload\Homepage;

/**
 * @covers \WP_Rocket\Preload\Process::is_mobile_preload_enabled
 * @group Preload
 */
class Test_preload extends TestCase {

	public function testShouldNotPreloadWhenInvalidUrls() {
		$preload_process = $this->createMock( Full_Process::class );
		$preload_process
			->expects( $this->exactly( 3 ) )
			->method( 'format_item' )
			->willReturn( false );
		$preload_process
			->expects( $this->never() )
			->method( 'is_mobile_preload_enabled' );

		$preload = new Homepage( $preload_process );

		// No URLs.
		$preload->preload( [] );

		// Invalid URLs.
		$preload->preload(
			[
				666,
				[],
				[ 'src' => 'foobar' ],
			]
		);
	}

	public function testShouldPreloadWhenValidUrls() {
		$queue     = [];
		$home_urls = [
			[ 'url' => 'https://wordpress.org' ],
			[ 'url' => 'https://wordpress.org/foobar/' ],
			[ 'url' => 'https://wordpress.org/category/barbaz/', 'mobile' => 1 ],
		];

		// Stubs.
		$preload_process = $this->createMock( Full_Process::class );
		$preload_process
			->expects( $this->exactly( 3 ) )
			->method( 'format_item' )
			->willReturnOnConsecutiveCalls(
				[ 'url' => 'https://wordpress.org', 'mobile' => false ],
				[ 'url' => 'https://wordpress.org/foobar/', 'mobile' => false ],
				[ 'url' => 'https://wordpress.org/category/barbaz/', 'mobile' => true ]
			);
		$preload_process
			->expects( $this->once() )
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
		Functions\when( 'trailingslashit' )->alias( function( $url ) {
			return rtrim( $url, '/' ) . '/';
		} );
		Functions\when( 'set_transient' )->justReturn( null );

		// Stubs for $this->get_urls().
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'wp_remote_get' )->alias( function( $url, $args = [] ) {
			$mobile_sub = ! empty( $args['user-agent'] ) && strpos( $args['user-agent'], 'iPhone' ) ? '/mobile' : '';
			$home_url   = 'https://wordpress.org' . $mobile_sub;
			switch ( $url ) {
				case 'https://wordpress.org':
					return [ 'body' => sprintf( '<a href="%1$s"><a href="%1$s/fr"><a href="%1$s/es">', $home_url ) ];
				case 'https://wordpress.org/foobar/':
					return [ 'body' => sprintf( '<a href="%1$s/de"><a href="%1$s/es"><a href="https://toto.org">', $home_url ) ];
				case 'https://wordpress.org/category/barbaz/':
					return [ 'body' => sprintf( '<a href="%1$s"><a href="%1$s/it">', $home_url ) ];
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

		// Stubs for $this->should_preload().
		Functions\when( 'get_rocket_parse_url' )->alias( function( $url ) {
			if ( ! is_string( $url ) ) {
				return;
			}
			$def = [
				'host'     => '',
				'path'     => '',
				'scheme'   => '',
				'query'    => '',
				'fragment' => '',
			];
			return array_intersect_key( array_merge( $def, parse_url( $url ) ), $def );
		} );
		Functions\when( 'home_url' )->justReturn( 'https://wordpress.org/' );
		Functions\when( 'rocket_add_url_protocol' )->returnArg();
		Functions\when( 'untrailingslashit' )->alias( function( $url ) {
			return rtrim( $url, '/' );
		} );
		Functions\when( 'get_rocket_cache_reject_uri' )->justReturn( '/foo/|/bar/|/(?:.+/)?embed/' );
		Functions\when( 'get_rocket_cache_query_string' )->justReturn( [] );

		$preload = new Homepage( $preload_process );

		$preload->preload( $home_urls );

		$this->assertContains( 'https://wordpress.org/fr', $queue );
		$this->assertContains( 'https://wordpress.org/es', $queue );
		$this->assertContains( 'https://wordpress.org/de', $queue );
		$this->assertContains( [ 'url' => 'https://wordpress.org/mobile', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://wordpress.org/mobile/fr', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://wordpress.org/mobile/es', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://wordpress.org/mobile/de', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://wordpress.org/mobile/it', 'mobile' => true ], $queue );
		$this->assertCount( 8, $queue );
	}
}
