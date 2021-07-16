<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Homepage;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Preload\FullProcess;
use WP_Rocket\Engine\Preload\Homepage;

/**
 * @covers \WP_Rocket\Engine\Preload\Homepage::preload
 * @group  Preload
 */
class Test_Preload extends TestCase {
	public function setUp() : void {
		parent::setUp();

		Functions\stubEscapeFunctions();
	}

	public function testShouldNotPreloadWhenInvalidUrls() {
		$preload_process = Mockery::mock( FullProcess::class );
		$preload_process
			->shouldReceive( 'format_item' )
			->times( 3 )
			->andReturn( [] );
		$preload_process
			->shouldReceive( 'is_mobile_preload_enabled' )
			->never();

		$preload = new Homepage( $preload_process );

		// No URLs.
		$preload->preload( [] );

		// Invalid URLs.
		$preload->preload(
			[
				1234,
				[],
				[ 'src' => 'foobar' ],
			]
		);
	}

	public function testShouldPreloadWhenValidUrls() {
		$queue     = [];
		$home_urls = [
			[ 'url' => 'https://example.org', 'mobile' => 0 ],
			[ 'url' => 'https://example.org/foobar/', 'mobile' => 0 ],
			[ 'url' => 'https://example.org/category/barbaz/', 'mobile' => 1 ],
		];

		// Stubs.
		$preload_process = Mockery::mock( FullProcess::class );
		$preload_process
			->shouldReceive( 'is_mobile_preload_enabled' )
			->once()
			->andReturn( true );
		$preload_process
			->shouldReceive( 'push_to_queue' )
			->andReturnUsing( function( $item ) use ( &$queue ) {
				$queue[] = $item;
			} );
		$preload_process
			->shouldReceive( 'save' )
			->once()
			->andReturnSelf();
		$preload_process
			->shouldReceive( 'dispatch' )
			->once()
			->andReturn( null );
		$preload_process
			->shouldReceive( 'format_item' )
			->andReturnArg(0);
		$preload_process
			->shouldReceive( 'get_item_user_agent' )
			->andReturnUsing( function ( $item ) {
				if ( $item['mobile'] ) {
					return 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1 WP Rocket/Preload';
				}
				return 'WP Rocket/Preload';
			} );

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = - 1 ) {
			return parse_url( $url, $component );
		} );
		Functions\when( 'set_transient' )->justReturn( null );

		// Stubs for $this->get_urls().
		Functions\when( 'wp_remote_get' )->alias( function( $url, $args = [] ) {
			$mobile_sub = ! empty( $args['user-agent'] ) && strpos( $args['user-agent'], 'iPhone' ) ? '/mobile' : '';
			$home_url   = 'https://example.org' . $mobile_sub;
			switch ( $url ) {
				case 'https://example.org':
					return [ 'body' => sprintf( '<a href="%1$s"><a href="%1$s/fr"><a href="%1$s/es">', $home_url ) ];
				case 'https://example.org/foobar/':
					return [ 'body' => sprintf( '<a href="%1$s/de"><a href="%1$s/es"><a href="https://toto.org">', $home_url ) ];
				case 'https://example.org/category/barbaz/':
					return [ 'body' => sprintf( '<a href="%1$s"><a href="%1$s/it">', $home_url ) ];
			}

			return false;
		} );
		Functions\when( 'get_transient' )->alias( function( $transient ) {
			return 'rocket_preload_errors' === $transient ? [] : false;
		} );
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->alias( function( $response ) {
			return is_array( $response ) && isset( $response['body'] ) ? $response['body'] : '';
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
		Functions\when( 'home_url' )->justReturn( 'https://example.org/' );
		Functions\when( 'rocket_add_url_protocol' )->returnArg();
		Functions\when( 'get_rocket_cache_reject_uri' )->justReturn( '/foo/|/bar/|/(?:.+/)?embed/' );
		Functions\when( 'get_rocket_cache_query_string' )->justReturn( [] );

		$preload = new Homepage( $preload_process );

		$preload->preload( $home_urls );

		$expected = [
			[ 'url' => 'https://example.org/fr', 'mobile' => false, 'source' => 'homepage' ],
			[ 'url' => 'https://example.org/es', 'mobile' => false, 'source' => 'homepage' ],
			[ 'url' => 'https://example.org/de', 'mobile' => false, 'source' => 'homepage' ],
			[ 'url' => 'https://example.org/mobile', 'mobile' => true, 'source' => 'homepage' ],
			[ 'url' => 'https://example.org/mobile/it', 'mobile' => true, 'source' => 'homepage' ],
			[ 'url' => 'https://example.org/mobile/fr', 'mobile' => true, 'source' => 'homepage' ],
			[ 'url' => 'https://example.org/mobile/es', 'mobile' => true, 'source' => 'homepage' ],
			[ 'url' => 'https://example.org/mobile/de', 'mobile' => true, 'source' => 'homepage' ],
		];

		$this->assertSame( $expected, $queue );
		$this->assertCount( 8, $queue );
	}
}
