<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Media\WebpSubscriber;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Subscriber\Media\Webp_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Media\Webp_Subscriber::convert_to_webp
 * @group Subscriber
 * @group WebP
 */
class Test_ConvertToWebp extends TestCase {
	/**
	 * @dataProvider matchProvider
	 */
	public function testShouldReturnIdenticalHtmlWhenCacheIsDisabledByOption( $original ) {
		// Mock the required objets for Webp_Subscriber.
		$mocks = $this->getConstructorMocks( 0 );

		// Make sure the filter to disable caching never runs.
		Filters\expectApplied( 'rocket_disable_webp_cache' )->never();

		$subscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $original, $subscriber->convert_to_webp( $original ) );
	}

	/**
	 * @dataProvider matchProvider
	 */
	public function testShouldReturnIdenticalHtmlWhenWebpCacheIsDisabledByFilter( $original ) {
		// Mock the required objets for Webp_Subscriber.
		$mocks = $this->getConstructorMocks();

		Functions\when( 'apache_request_headers' )
			->alias( function() {
				return [
					'Accept' => 'webp',
				];
			} );

		// Make sure the filter to disable caching runs once with the expected output value.
		Filters\expectApplied( 'rocket_disable_webp_cache' )
			->once()
			->andReturn( true ); // Simulate a filter.

		// Make sure the method get_extensions() never runs.
		Filters\expectApplied( 'rocket_file_extensions_for_webp' )->never();

		$subscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $original, $subscriber->convert_to_webp( $original ) );
	}

	/**
	 * @dataProvider matchProvider
	 */
	public function testShouldReturnIdenticalHtmlWhenNoWebpHeader( $original ) {
		// Mock the required objets for Webp_Subscriber.
		$mocks = $this->getConstructorMocks();

		Functions\when( 'apache_request_headers' )
			->alias( function() {
				return [
					'Accept' => '*/*',
				];
			} );

		// Make sure the filter to disable caching runs once with the expected output value.
		Filters\expectApplied( 'rocket_disable_webp_cache' )
			->once()
			->andReturn( true ); // Simulate a filter.

		// Make sure the method get_extensions() never runs.
		Filters\expectApplied( 'rocket_file_extensions_for_webp' )
			->never();

		$subscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $original, $subscriber->convert_to_webp( $original ) );
	}

	/**
	 * @dataProvider noFileExtensionsProvider
	 */
	public function testShouldReturnHtmlWithCommentWhenNoFileExtensions( $original, $expected ) {
		// Mock the required objets for Webp_Subscriber.
		$mocks = $this->getConstructorMocks();

		Functions\when( 'apache_request_headers' )
			->alias( function() {
				return [
					'Accept' => 'webp',
				];
			} );

		// Make sure the the method get_extensions() runs once and returns an empty array.
		Filters\expectApplied( 'rocket_file_extensions_for_webp' )
			->once()
			->andReturn( [] ); // Simulate a filter.

		// Make sure the function rocket_direct_filesystem() never runs.
		Functions\expect( 'rocket_direct_filesystem' )->never();

		$subscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertSame( $expected, $subscriber->convert_to_webp( $original ) );
	}

	/**
	 * @dataProvider noFileExtensionsProvider
	 */
	public function testShouldReturnHtmlWithCommentWhenNoAttributeNames( $original, $expected ) {
		// Mock the required objets for Webp_Subscriber.
		$mocks = $this->getConstructorMocks();

		Functions\when( 'apache_request_headers' )
			->alias( function() {
				return [
					'Accept' => 'webp',
				];
			} );

		// Make sure the the method get_attribute_names() runs once and returns an empty array.
		Filters\expectApplied( 'rocket_attributes_for_webp' )
			->once()
			->andReturn( [] ); // Simulate a filter.

		// Make sure the function rocket_direct_filesystem() never runs.
		Functions\expect( 'rocket_direct_filesystem' )->never();

		$subscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertSame( $expected, $subscriber->convert_to_webp( $original ) );
	}

	public function noFileExtensionsProvider() {
		return $this->getTestData( __DIR__, 'convert-to-webp-noextensions-attributes' );
	}

	/**
	 * @dataProvider noMatchProvider
	 */
	public function testShouldReturnHtmlWithCommentWhenNoMatches( $original, $expected ) {
		// Mock the required objets for Webp_Subscriber.
		$mocks = $this->getConstructorMocks();

		Functions\when( 'apache_request_headers' )
			->alias( function() {
				return [
					'Accept' => 'webp',
				];
			} );

		// Make sure the function rocket_direct_filesystem() never runs.
		Functions\expect( 'rocket_direct_filesystem' )->never();

		$subscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertSame( $expected, $subscriber->convert_to_webp( $original ) );
	}

	public function noMatchProvider() {
		return $this->getTestData( __DIR__, 'convert-to-webp-nomatch' );
	}

	/**
	 * @dataProvider matchProvider
	 */
	public function testShouldReturnModifiedHtml( $original, $expected ) {
		// Mock the required objets for Webp_Subscriber.
		$mocks = $this->getConstructorMocks();

		Functions\when('wp_upload_dir')->justReturn([
			'basedir' => '/Internal/path/to/root/wp-content/uploads/'
		]);


		Functions\when( 'apache_request_headers' )
			->alias( function() {
				return [
					'Accept' => 'webp',
				];
			} );

		// rocket_direct_filesystem().
		Functions\when( 'rocket_direct_filesystem' )->alias( function() {
			$uploads_path = '/Internal/path/to/root/wp-content/uploads/';
			$filesystem   = Mockery::mock( 'WP_Filesystem_Direct' );

			$data = [
				[ $uploads_path . '2019/09/one-image.webp', true ],
				[ $uploads_path . '2019/09/one-image.png.webp', false ],
				[ $uploads_path . '2017/02/apple-touch-icon.webp', false ],
				[ $uploads_path . '2017/02/apple-touch-icon.png.webp', true ],
				[ $uploads_path . '2017/02/favicon-32x32.webp', true ],
				[ $uploads_path . '2017/02/favicon-32x32.png.webp', false ],
				[ $uploads_path . '2017/02/mstile-144x144.webp', false ],
				[ $uploads_path . '2017/02/mstile-144x144.png.webp', false ],
				[ $uploads_path . '2019/09/one-image-60x60.webp', false ],
				[ $uploads_path . '2019/09/one-image-60x60.png.webp', false ],
				[ $uploads_path . '2017/02/stats-php.webp', false ],
				[ $uploads_path . '2017/02/stats-php.gif.webp', true ],
			];

			foreach ( $data as $values ) {
				$filesystem
					->shouldReceive( 'exists' )
					->with( $values[0] )
					->andReturn( $values[1] );
			}

			return $filesystem;
		} );

		// WP functions.
		$this->mockWpFunctions();

		$subscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $expected, $subscriber->convert_to_webp( $original ) );
	}

	/**
	 * @dataProvider matchCustomUploadProvider
	 */
	public function testShouldReturnModifiedHtmlWithCustomUpload( $original, $expected ) {
		// Mock the required objets for Webp_Subscriber.
		$mocks = $this->getConstructorMocks();

		Functions\when('wp_upload_dir')->justReturn([
			'basedir' => '/Internal/path/to/root/custom-uploads/',
			'baseurl' => 'https://example.org/custom-uploads/'
		]);


		Functions\when( 'apache_request_headers' )
			->alias( function() {
				return [
					'Accept' => 'webp',
				];
			} );

		// rocket_direct_filesystem().
		Functions\when( 'rocket_direct_filesystem' )->alias( function() {
			$uploads_path = '/Internal/path/to/root/wp-content/uploads/';
			$filesystem   = Mockery::mock( 'WP_Filesystem_Direct' );

			$data = [
				[ $uploads_path . '2019/09/one-image.webp', true ],
				[ $uploads_path . '2019/09/one-image.png.webp', false ],
				[ $uploads_path . '2017/02/apple-touch-icon.webp', false ],
				[ $uploads_path . '2017/02/apple-touch-icon.png.webp', true ],
				[ $uploads_path . '2017/02/favicon-32x32.webp', true ],
				[ $uploads_path . '2017/02/favicon-32x32.png.webp', false ],
				[ $uploads_path . '2017/02/mstile-144x144.webp', false ],
				[ $uploads_path . '2017/02/mstile-144x144.png.webp', false ],
				[ $uploads_path . '2019/09/one-image-60x60.webp', false ],
				[ $uploads_path . '2019/09/one-image-60x60.png.webp', false ],
				[ $uploads_path . '2017/02/stats-php.webp', false ],
				[ $uploads_path . '2017/02/stats-php.gif.webp', true ],
			];

			foreach ( $data as $values ) {
				$filesystem
					->shouldReceive( 'exists' )
					->with( $values[0] )
					->andReturn( $values[1] );
			}

			return $filesystem;
		} );

		// WP functions.
		$this->mockWpFunctions(true);

		$subscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $expected, $subscriber->convert_to_webp( $original ) );
	}

	public function matchProvider() {
		return $this->getTestData( __DIR__, 'convert-to-webp-match' );
	}

	public function matchCustomUploadProvider() {
		return $this->getTestData( __DIR__, 'convert-to-webp-match-custom-upload' );
	}

	private function mockWpFunctions($custom_path = false ) {

		if( ! $custom_path ) {
			Functions\expect( 'rocket_get_constant' )
				->once()
				->with( 'WP_CONTENT_DIR' )
				->andReturn( '/Internal/path/to/root/wp-content' );
		}

		// wp_parse_url().
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component ) {
			return parse_url( $url, $component );
		} );

		// set_url_scheme().
		Functions\when( 'set_url_scheme' )->alias( function( $url, $scheme = null ) {
			$orig_scheme = $scheme;

			if ( ! $scheme ) {
				$scheme = 'https';
			} elseif ( $scheme === 'admin' || $scheme === 'login' || $scheme === 'login_post' || $scheme === 'rpc' ) {
				$scheme = 'https';
			} elseif ( $scheme !== 'http' && $scheme !== 'https' && $scheme !== 'relative' ) {
				$scheme = 'https';
			}

			$url = trim( $url );
			if ( substr( $url, 0, 2 ) === '//' ) {
				$url = 'http:' . $url;
			}

			if ( 'relative' == $scheme ) {
				$url = ltrim( preg_replace( '#^\w+://[^/]*#', '', $url ) );
				if ( $url !== '' && $url[0] === '/' ) {
					$url = '/' . ltrim( $url, "/ \t\n\r\0\x0B" );
				}
			} else {
				$url = preg_replace( '#^\w+://#', $scheme . '://', $url );
			}

			return $url;
		} );

		// site_url().
		Functions\when( 'site_url' )->alias( function( $path = '', $scheme = null ) {
			$url = set_url_scheme( 'https://example.com', $scheme );

			if ( $path && is_string( $path ) ) {
				$url .= '/' . ltrim( $path, '/' );
			}

			return $url;
		} );

		// content_url().
		Functions\when( 'content_url' )->justReturn( 'https://example.com/wp-content/' );
	}
}
