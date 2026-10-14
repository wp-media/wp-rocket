<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Cache;

use Brain\Monkey\Functions;
use Mockery;
use ReflectionMethod;
use WP_Rocket\Buffer\Cache;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Buffer\Cache::serve_cache_file and ::serve_gzip_cache_file
 *
 * @group Buffer
 */
class Test_ServeCacheFile extends TestCase {

	/**
	 * Should return false, without output, when the cache file is gone by the time it is served.
	 *
	 * @dataProvider providerMethods
	 *
	 * @param string $method Name of the serving method.
	 * @param string $suffix Suffix of the cache file.
	 */
	public function testShouldReturnFalseWithoutOutputWhenCacheFileIsMissing( $method, $suffix ) {
		Functions\expect( 'header' )->never();

		$result = $this->serve( $method, sys_get_temp_dir() . '/wpr-missing-cache-file/index-https.html' . $suffix );

		$this->assertFalse( $result['result'] );
		$this->assertSame( '', $result['output'] );
	}

	/**
	 * Should return false, without output, when the cache file exists but cannot be read.
	 *
	 * @dataProvider providerMethods
	 *
	 * @param string $method Name of the serving method.
	 * @param string $suffix Suffix of the cache file.
	 */
	public function testShouldReturnFalseWithoutOutputWhenCacheFileCannotBeRead( $method, $suffix ) {
		$file = tempnam( sys_get_temp_dir(), 'wpr-cache' ) . $suffix;
		file_put_contents( $file, 'content' );
		chmod( $file, 0000 );

		if ( is_readable( $file ) ) {
			unlink( $file );
			$this->markTestSkipped( 'Permissions are not enforced for this user.' );
		}

		Functions\expect( 'header' )->once()->with( Mockery::pattern( '/^Last-Modified: /' ) );
		Functions\expect( 'header_remove' )->once()->with( 'Last-Modified' );

		$result = $this->serve( $method, $file );

		chmod( $file, 0600 );
		unlink( $file );

		$this->assertFalse( $result['result'] );
		$this->assertSame( '', $result['output'] );
	}

	/**
	 * Provides the serving methods with the suffix of their cache file.
	 *
	 * @return array
	 */
	public function providerMethods() {
		return [
			'plain cache file' => [ 'serve_cache_file', '' ],
			'gzip cache file'  => [ 'serve_gzip_cache_file', '_gzip' ],
		];
	}

	/**
	 * Calls a private serving method and captures what it outputs.
	 *
	 * @param string $method         Name of the serving method.
	 * @param string $cache_filepath Path to the cache file.
	 *
	 * @return array
	 */
	private function serve( $method, $cache_filepath ) {
		$tests = Mockery::mock( Tests::class );
		$tests->shouldReceive( 'get_raw_request_uri' )->andReturn( '/some-page/' );

		Functions\when( 'apache_request_headers' )->justReturn( [] );

		$cache = new Cache(
			$tests,
			Mockery::mock( Config::class ),
			[ 'cache_dir_path' => '/tmp/wp-rocket-cache/' ]
		);

		$serve = new ReflectionMethod( Cache::class, $method );

		if ( PHP_VERSION_ID < 80100 ) {
			$serve->setAccessible( true );
		}

		ob_start();
		$result = $serve->invoke( $cache, $cache_filepath );
		$output = ob_get_clean();

		return [
			'result' => $result,
			'output' => $output,
		];
	}
}
