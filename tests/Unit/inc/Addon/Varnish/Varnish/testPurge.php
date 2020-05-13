<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\Varnish\Varnish;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Addon\Varnish\Varnish;

/**
 * @covers WP_Rocket\Addon\Varnish\Varnish::purge
 * @group  Varnish
 * @group  Addon
 */
class Test_Purge extends TestCase {

	public function testShouldSendRequestOnceWithDefaultValues() {
		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[
				'varnish_auto_purge',
				0,
				0,
			],
		];

		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		$varnish = new Varnish( $options );

		Functions\when( 'get_rocket_parse_url' )->alias( function( $url ) {
			$url = parse_url( $url );

			$host     = isset( $url['host'] ) ? strtolower( urldecode( $url['host'] ) ) : '';
			$path     = isset( $url['path'] ) ? urldecode( $url['path'] ) : '';
			$scheme   = isset( $url['scheme'] ) ? urldecode( $url['scheme'] ) : '';
			$query    = isset( $url['query'] ) ? urldecode( $url['query'] ) : '';
			$fragment = isset( $url['fragment'] ) ? urldecode( $url['fragment'] ) : '';

			return [
				'host'     => $host,
				'path'     => $path,
				'scheme'   => $scheme,
				'query'    => $query,
				'fragment' => $fragment,
			];
		} );
		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				'http://example.org',
				[
					'method'      => 'PURGE',
					'blocking'    => false,
					'redirection' => 0,
					'headers'     => [
						'host'           => 'example.org',
						'X-Purge-Method' => 'default',
					],
				]
			);

		$varnish->purge( 'http://example.org' );
	}

	public function testShouldSendRequestOnceWithCustomVarnishIP() {
		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[
				'varnish_auto_purge',
				0,
				0,
			],
		];

		Filters\expectApplied( 'rocket_varnish_ip' )
			->once()
			->andReturn(
				[
					'localhost',
				]
			);

		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		$varnish = new Varnish( $options );

		Functions\when( 'get_rocket_parse_url' )->alias( function( $url ) {
			$url = parse_url( $url );

			$host     = isset( $url['host'] ) ? strtolower( urldecode( $url['host'] ) ) : '';
			$path     = isset( $url['path'] ) ? urldecode( $url['path'] ) : '';
			$scheme   = isset( $url['scheme'] ) ? urldecode( $url['scheme'] ) : '';
			$query    = isset( $url['query'] ) ? urldecode( $url['query'] ) : '';
			$fragment = isset( $url['fragment'] ) ? urldecode( $url['fragment'] ) : '';

			return [
				'host'     => $host,
				'path'     => $path,
				'scheme'   => $scheme,
				'query'    => $query,
				'fragment' => $fragment,
			];
		} );
		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				'http://localhost/.*',
				[
					'method'      => 'PURGE',
					'blocking'    => false,
					'redirection' => 0,
					'headers'     => [
						'host'           => 'example.org',
						'X-Purge-Method' => 'regex',
					],
				]
			);

		$varnish->purge( 'http://example.org/?regex' );
	}

	public function testShouldSendRequestTwiceWhenArrayVarnishIp() {
		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[
				'varnish_auto_purge',
				0,
				0,
			],
		];

		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		$varnish = new Varnish( $options );

		Functions\when( 'get_rocket_parse_url' )->alias( function( $url ) {
			$url = parse_url( $url );

			$host     = isset( $url['host'] ) ? strtolower( urldecode( $url['host'] ) ) : '';
			$path     = isset( $url['path'] ) ? urldecode( $url['path'] ) : '';
			$scheme   = isset( $url['scheme'] ) ? urldecode( $url['scheme'] ) : '';
			$query    = isset( $url['query'] ) ? urldecode( $url['query'] ) : '';
			$fragment = isset( $url['fragment'] ) ? urldecode( $url['fragment'] ) : '';

			return [
				'host'     => $host,
				'path'     => $path,
				'scheme'   => $scheme,
				'query'    => $query,
				'fragment' => $fragment,
			];
		} );
		Filters\expectApplied( 'rocket_varnish_ip' )
			->once()
			->andReturn(
				[
					'localhost',
					'127.0.0.1',
				]
			);

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				'http://localhost/about/',
				[
					'method'      => 'PURGE',
					'blocking'    => false,
					'redirection' => 0,
					'headers'     => [
						'host'           => 'example.org',
						'X-Purge-Method' => 'default',
					],
				]
			);

		Functions\expect( 'wp_remote_request' )
			->once()
			->with(
				'http://127.0.0.1/about/',
				[
					'method'      => 'PURGE',
					'blocking'    => false,
					'redirection' => 0,
					'headers'     => [
						'host'           => 'example.org',
						'X-Purge-Method' => 'default',
					],
				]
			);

		$varnish->purge( 'http://example.org/about/' );
	}
}
