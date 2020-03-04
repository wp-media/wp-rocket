<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Media\WebpSubscriber;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Media\Webp_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Media\Webp_Subscriber::convert_to_webp
 * @group Subscriber
 */
class Test_ConvertToWebp extends TestCase {

	public function testShouldReturnIdenticalHtmlWhenCacheIsDisabledByOption() {
		$html = $this->getMatchingContents();

		// Mock the required objets for Webp_Subscriber.
		$mocks = $this->getConstructorMocks( 0 );

		// Make sure the filter to disable caching never runs.
		Filters\expectApplied( 'rocket_disable_webp_cache' )->never();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $html, $webpSubscriber->convert_to_webp( $html ) );
	}

	public function testShouldReturnIdenticalHtmlWhenWebpCacheIsDisabledByFilter() {
		$html = $this->getMatchingContents();

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

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $html, $webpSubscriber->convert_to_webp( $html ) );
	}

	public function testShouldReturnIdenticalHtmlWhenNoWebpHeader() {
		$html = $this->getMatchingContents();

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

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $html, $webpSubscriber->convert_to_webp( $html ) );
	}

	public function testShouldReturnIdenticalHtmlWhenNoFileExtensions() {
		$html = $this->getMatchingContents();

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

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $html, $webpSubscriber->convert_to_webp( $html ) );
	}

	public function testShouldReturnIdenticalHtmlWhenNoAttributeNames() {
		$html = $this->getMatchingContents();

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

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $html, $webpSubscriber->convert_to_webp( $html ) );
	}

	public function testShouldReturnIdenticalHtmlWhenNoImageExtensionsFound() {
		$html = $this->getContentsNotMatchingFileExtensions();

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

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $html, $webpSubscriber->convert_to_webp( $html ) );
	}

	public function testShouldReturnIdenticalHtmlWhenNoImageAttributesFound() {
		$html = $this->getContentsNotMatchingAttributes();

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

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $html, $webpSubscriber->convert_to_webp( $html ) );
	}

	public function testShouldReturnIdenticalHtmlWhenImagesHaveEmptyAttributes() {
		$html = $this->getContentsWithEmptySources();

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

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $html, $webpSubscriber->convert_to_webp( $html ) );
	}

	public function testShouldReturnModifiedHtml() {
		// Mock the required objets for Webp_Subscriber.
		$mocks = $this->getConstructorMocks();

		Functions\when( 'apache_request_headers' )
			->alias( function() {
				return [
					'Accept' => 'webp',
				];
			} );

		// rocket_direct_filesystem().
		Functions\when( 'rocket_direct_filesystem' )->alias( function() {
			$uploads_path = '/Internal/path/to/root/wp-content/uploads/';
			$filesystem   = $this->getMockBuilder( 'WP_Filesystem_Direct' )
			                     ->setMethods( [ 'exists' ] )
			                     ->getMock();
			$filesystem
				->method( 'exists' )
				->will(
					$this->returnValueMap(
						[
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
						]
					)
				);

			return $filesystem;
		} );

		// WP functions.
		$this->mockWpFunctions();

		$original_html = $this->getMatchingContents();
		$expected_html = $this->getExpectedContents();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		// Assert that the HTML is the same.
		$this->assertSame( $expected_html, $webpSubscriber->convert_to_webp( $original_html ) );
	}

	private function mockWpFunctions() {
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_CONTENT_DIR' )
			->andReturn( '/Internal/path/to/root/wp-content' );

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

	/**
	 * Get a HTML sample containing images.
	 */
	private function getMatchingContents() {
		return file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Subscriber/Media/WebpSubscriber/html/matching.html' );
	}

	/**
	 * Get a HTML sample not containing images with the right file extension.
	 */
	private function getContentsNotMatchingFileExtensions() {
		return file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Subscriber/Media/WebpSubscriber/html/not-matching-file-extensions.html' );
	}

	/**
	 * Get a HTML sample not containing images with the right attributes.
	 */
	private function getContentsNotMatchingAttributes() {
		return file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Subscriber/Media/WebpSubscriber/html/not-matching-attributes.html' );
	}

	/**
	 * Get a HTML sample containing images that have empty attributes.
	 */
	private function getContentsWithEmptySources() {
		return file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Subscriber/Media/WebpSubscriber/html/not-matching-with-empty-sources.html' );
	}

	/**
	 * Get the HTML contents expected after replacing some image URLs.
	 */
	private function getExpectedContents() {
		return file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Subscriber/Media/WebpSubscriber/html/expected.html' );
	}
}
