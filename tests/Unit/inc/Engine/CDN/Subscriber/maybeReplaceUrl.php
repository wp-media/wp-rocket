<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Subscriber;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::maybe_replace_url
 * @group  CDN
 */
class Test_MaybeReplaceUrl extends TestCase {
	private $cdn;
	private $options;
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		Functions\when( 'get_rocket_parse_url' )->alias( function( $url ) {
			$parsed = parse_url( $url );

			$host     = isset( $parsed['host'] ) ? strtolower( urldecode( $parsed['host'] ) ) : '';
			$path     = isset( $parsed['path'] ) ? urldecode( $parsed['path'] ) : '';
			$scheme   = isset( $parsed['scheme'] ) ? urldecode( $parsed['scheme'] ) : '';
			$query    = isset( $parsed['query'] ) ? urldecode( $parsed['query'] ) : '';
			$fragment = isset( $parsed['fragment'] ) ? urldecode( $parsed['fragment'] ) : '';

			return [
				'host'     => $host,
				'path'     => $path,
				'scheme'   => $scheme,
				'query'    => $query,
				'fragment' => $fragment,
			];
		} );

		$this->cdn        = Mockery::mock( CDN::class );
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new Subscriber(
			$this->options,
			$this->cdn
		);
	}

	public function testShouldReturnOriginalWhenDONOTROCKETOPTIMIZE() {
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'DONOTROCKETOPTIMIZE' )
			->andReturn( true );

		$this->assertSame(
			'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css',
			$this->subscriber->maybe_replace_url( 'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css', [ 'all' ] )
		);
	}

	public function testShouldReturnOriginalWhenCDNDisabled() {
		$this->options->shouldReceive( 'get' )
			->andReturn( false );

		$this->assertSame(
			'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css',
			$this->subscriber->maybe_replace_url( 'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css', [ 'all' ] )
		);
	}

	public function testShouldReturnOriginalWhenCDNDisabledOnPost() {
		$this->options->shouldReceive( 'get' )
			->andReturn( true );

		Functions\when( 'is_rocket_post_excluded_option' )->justReturn( true );

		$this->assertSame(
			'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css',
			$this->subscriber->maybe_replace_url( 'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css', [ 'all' ] )
		);
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'maybe-replace-url' );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldMaybeReplaceURL( $original, $zones, $cdn_urls, $site_url, $expected ) {
		$this->options->shouldReceive( 'get' )
			->andReturn( true );

		Functions\when( 'is_rocket_post_excluded_option' )->justReturn( false );

		$this->cdn->shouldReceive( 'get_cdn_urls' )
			->zeroOrMoreTimes()
			->andReturn( $cdn_urls );

			Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
				if ( strpos( $url, 'http://' ) !== false || strpos( $url, 'https://' ) !== false ) {
					return $url;
				}

				if ( substr( $url, 0, 2 ) === '//' ) {
					return 'http:' . $url;
				}

				return 'http://' . $url;
			} );
		Functions\when( 'site_url' )->justReturn( $site_url );

		$this->assertSame(
			$expected,
			$this->subscriber->maybe_replace_url( $original, $zones )
		);
	}
}
