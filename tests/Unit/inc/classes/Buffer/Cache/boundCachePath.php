<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Cache;

use ReflectionMethod;
use WP_Rocket\Buffer\Cache;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Buffer\Cache::bound_cache_path
 *
 * @group  Buffer
 */
class Test_BoundCachePath extends TestCase {
	/**
	 * Cache instance under test.
	 *
	 * @var Cache
	 */
	private $cache;

	/**
	 * Accessor for the private method under test.
	 *
	 * @var ReflectionMethod
	 */
	private $method;

	/**
	 * Builds the instance and opens the private method.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->cache = new Cache(
			\Mockery::mock( Tests::class ),
			\Mockery::mock( Config::class ),
			[ 'cache_dir_path' => '/cache/wp-rocket/' ]
		);

		$this->method = new ReflectionMethod( Cache::class, 'bound_cache_path' );
		$this->method->setAccessible( true );
	}

	/**
	 * Invokes the private method under test.
	 *
	 * @param string $path Path to bound.
	 * @return string
	 */
	private function bound( $path ) {
		return $this->method->invoke( $this->cache, $path );
	}

	/**
	 * A path whose components all fit is returned untouched.
	 */
	public function testShouldReturnPathUnchangedWhenEveryComponentFits() {
		$path = '/cache/wp-rocket/example.com/shop/item';

		$this->assertSame( $path, $this->bound( $path ) );
	}

	/**
	 * 239 bytes is the last verbatim length.
	 */
	public function testShouldKeepComponentOf239BytesVerbatim() {
		$component = str_repeat( 'a', 239 );
		$path      = '/cache/wp-rocket/example.com/' . $component;

		$this->assertSame( $path, $this->bound( $path ) );
	}

	/**
	 * At 240 bytes the component is replaced by prefix, "~" and the md5 of the whole component.
	 */
	public function testShouldBoundComponentOf240Bytes() {
		$component = str_repeat( 'a', 240 );
		$expected  = substr( $component, 0, 207 ) . '~' . md5( $component );

		$this->assertSame(
			'/cache/wp-rocket/example.com/' . $expected,
			$this->bound( '/cache/wp-rocket/example.com/' . $component )
		);
		$this->assertSame( 240, strlen( $expected ) );
	}

	/**
	 * Two components differing only past the retained prefix stay distinct.
	 */
	public function testShouldKeepOverlongComponentsDistinct() {
		$one = str_repeat( 'a', 300 );
		$two = str_repeat( 'a', 299 ) . 'b';

		$this->assertNotSame(
			$this->bound( '/cache/wp-rocket/example.com/' . $one ),
			$this->bound( '/cache/wp-rocket/example.com/' . $two )
		);
	}

	/**
	 * Every overlong component in the path is bounded, not only the last one.
	 */
	public function testShouldBoundEveryOverlongComponent() {
		$long   = str_repeat( 'a', 300 );
		$result = $this->bound( '/cache/wp-rocket/example.com/' . $long . '/' . $long );

		foreach ( explode( '/', substr( $result, strlen( '/cache/wp-rocket/' ) ) ) as $component ) {
			$this->assertLessThanOrEqual( 240, strlen( $component ) );
		}
	}

	/**
	 * The cache root is server owned and is never rewritten, however long it is.
	 */
	public function testShouldNeverRewriteTheCacheRoot() {
		$result = $this->bound( '/cache/wp-rocket/example.com/shop' );

		$this->assertStringStartsWith( '/cache/wp-rocket/', $result );
	}

	/**
	 * A path outside the configured root is returned untouched.
	 */
	public function testShouldReturnPathUnchangedWhenOutsideTheRoot() {
		$path = '/somewhere/else/' . str_repeat( 'a', 300 );

		$this->assertSame( $path, $this->bound( $path ) );
	}

	/**
	 * Purge and the writer share one rule, so they cannot drift apart.
	 *
	 * @dataProvider componentProvider
	 *
	 * @param string $component Component to bound.
	 */
	public function testShouldExposeTheRuleForPurge( $component ) {
		$this->assertSame(
			Cache::bound_path_component( $component ),
			ltrim( Cache::bound_path_components( '/' . $component ), '/' )
		);
	}

	/**
	 * Components on both sides of the 240 byte boundary.
	 *
	 * @return array
	 */
	public function componentProvider() {
		return [
			'short'       => [ 'item' ],
			'239 bytes'   => [ str_repeat( 'b', 239 ) ],
			'exactly 240' => [ str_repeat( 'b', 240 ) ],
			'300 bytes'   => [ str_repeat( 'b', 300 ) ],
		];
	}

	/**
	 * The bounded basename must still fit ".html_gzip_temp", the longest name written to disk.
	 */
	public function testShouldLeaveRoomForEverySuffix() {
		$bounded = Cache::bound_path_component( str_repeat( 'a', 300 ) );

		$this->assertLessThanOrEqual( 255, strlen( $bounded . '.html_gzip_temp' ) );
	}
}
