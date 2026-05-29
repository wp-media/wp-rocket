<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Cache;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use ReflectionMethod;
use WP_Rocket\Buffer\Cache;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests as BufferTests;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Buffer\Cache::send_headers() as used by serve_gzip_cache_file().
 *
 * These tests mirror the ServeCacheFile test scenarios for the gzip serving path,
 * confirming that the rocket_cache_http_headers filter applies identically for gzip responses.
 *
 * @group Buffer
 */
class Test_ServeGzipCacheFile extends TestCase {

	private $buffer_config;
	private $buffer_tests;
	private $cache;
	private $send_headers;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->buffer_config = Mockery::mock( Config::class );
		$this->buffer_tests  = Mockery::mock( BufferTests::class, [ $this->buffer_config ] );

		$this->buffer_config->shouldReceive( 'get_config' )
			->with( 'cache_mobile' )
			->andReturn( false )
			->byDefault();
		$this->buffer_config->shouldReceive( 'get_config' )
			->with( 'do_caching_mobile_files' )
			->andReturn( false )
			->byDefault();
		$this->buffer_config->shouldReceive( 'get_config' )
			->with( 'cache_webp' )
			->andReturn( false )
			->byDefault();

		$this->cache = new Cache(
			$this->buffer_tests,
			$this->buffer_config,
			[ 'cache_dir_path' => '/tmp/rocket-cache' ]
		);

		$this->send_headers = new ReflectionMethod( Cache::class, 'send_headers' );
		$this->send_headers->setAccessible( true );
	}

	/**
	 * Test 1: Last-Modified header value is included in the array passed to the rocket_cache_http_headers filter.
	 */
	public function testLastModifiedHeaderPassedThroughFilter(): void {
		$headers_received = null;

		Filters\expectApplied( 'rocket_cache_http_headers' )
			->once()
			->andReturnUsing(
				function ( $headers ) use ( &$headers_received ) {
					$headers_received = $headers;
					return $headers;
				}
			);

		Functions\when( 'header' )->justReturn( null );

		$this->send_headers->invoke(
			$this->cache,
			[ 'Last-Modified' => 'Fri, 01 Jan 2021 00:00:00 GMT' ]
		);

		$this->assertIsArray( $headers_received );
		$this->assertArrayHasKey( 'Last-Modified', $headers_received );
		$this->assertSame( 'Fri, 01 Jan 2021 00:00:00 GMT', $headers_received['Last-Modified'] );
	}

	/**
	 * Test 2: Filter callback adding a header (X-Foo: foo) results in header() being called with that header.
	 *
	 * Verifies that the filter return value is fully applied: header() is called exactly once
	 * with the header string added by the callback.
	 */
	public function testFilterAddedHeaderIsSentViaHeader(): void {
		Filters\expectApplied( 'rocket_cache_http_headers' )
			->once()
			->andReturnUsing(
				function ( $headers ) {
					$headers['X-Foo'] = 'foo';
					return $headers;
				}
			);

		Functions\expect( 'header' )
			->once()
			->with( 'X-Foo: foo' );

		$this->send_headers->invoke( $this->cache, [] );
	}

	/**
	 * Test 3: 304 path — Expires and Cache-Control are passed through the rocket_cache_http_headers filter.
	 */
	public function test304PathExpiresAndCacheControlPassedThroughFilter(): void {
		$headers_received = null;

		Filters\expectApplied( 'rocket_cache_http_headers' )
			->once()
			->andReturnUsing(
				function ( $headers ) use ( &$headers_received ) {
					$headers_received = $headers;
					return $headers;
				}
			);

		Functions\when( 'header' )->justReturn( null );

		$this->send_headers->invoke(
			$this->cache,
			[
				'Expires'       => 'Thu, 01 Jan 1970 00:00:00 GMT',
				'Cache-Control' => 'no-cache, must-revalidate',
			]
		);

		$this->assertIsArray( $headers_received );
		$this->assertArrayHasKey( 'Expires', $headers_received );
		$this->assertArrayHasKey( 'Cache-Control', $headers_received );
		$this->assertSame( 'no-cache, must-revalidate', $headers_received['Cache-Control'] );
	}

	/**
	 * Test 4: Filter returning a non-array value does not cause a fatal error.
	 */
	public function testFilterReturningNonArrayDoesNotCauseFatal(): void {
		Filters\expectApplied( 'rocket_cache_http_headers' )
			->once()
			->andReturn( 'not-an-array' );

		Functions\when( 'header' )->justReturn( null );

		// Cast of 'not-an-array' to array gives [0 => 'not-an-array']; integer key
		// fails the is_string($name) guard — no header() call is reached.
		$this->send_headers->invoke( $this->cache, [ 'Last-Modified' => 'Mon, 01 Jan 2024 00:00:00 GMT' ] );

		// If we get here, no exception or fatal was thrown — that is the assertion.
		$this->addToAssertionCount( 1 );
	}

	/**
	 * Test 5: Filter callback adding a non-string key or value is silently skipped.
	 *
	 * Entries with integer keys or non-string values must be dropped.
	 * Only the valid string key/value entry (X-Valid: ok) should be sent via header().
	 * header() must be called exactly once — only for the valid entry.
	 */
	public function testNonStringKeyOrValueIsSkipped(): void {
		Filters\expectApplied( 'rocket_cache_http_headers' )
			->once()
			->andReturnUsing(
				function ( $headers ) {
					// Integer key — should be skipped.
					$headers[] = 'value-with-int-key';
					// Non-string value — should be skipped.
					$headers['X-Int'] = 42;
					// Valid entry — should pass through.
					$headers['X-Valid'] = 'ok';
					return $headers;
				}
			);

		Functions\expect( 'header' )
			->once()
			->with( 'X-Valid: ok' );

		$this->send_headers->invoke( $this->cache, [] );
	}
}
