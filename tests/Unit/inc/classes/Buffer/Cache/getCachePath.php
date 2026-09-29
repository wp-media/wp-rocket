<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Cache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Buffer\Cache;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Buffer\Cache::get_cache_path
 *
 * @group  Buffer
 */
class Test_GetCachePath extends TestCase {
	/**
	 * The cookies of the request as they were before the test.
	 *
	 * @var array
	 */
	private $was_cookies = [];

	/**
	 * Keeps the cookies of the request, which the tests here write over.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->was_cookies = $_COOKIE;
	}

	/**
	 * Puts them back.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		$_COOKIE = $this->was_cookies;

		parent::tearDown();
	}

	/**
	 * Builds the path for a request that carries the given cookies.
	 *
	 * @param array      $config      What the buffer config holds.
	 * @param array      $cookies     Cookies this call is given.
	 * @param array|null $superglobal Cookies in the environment, where they differ from those.
	 *
	 * @return string
	 */
	private function path_for( array $config, array $cookies, ?array $superglobal = null ) {
		Functions\when( 'is_ssl' )->justReturn( false );

		// The request is read once to answer it and once to store it, and the second read happens after
		// everything on the site has had a chance to write to the environment.
		$_COOKIE = null === $superglobal ? $cookies : $superglobal;

		$config_mock = Mockery::mock( Config::class );
		$config_mock->shouldReceive( 'get_config' )->andReturnUsing(
			function ( $name ) use ( $config ) {
				return $config[ $name ] ?? false;
			}
		);

		$config_mock->shouldReceive( 'get_host' )->andReturn( 'example.org' );

		$tests = Mockery::mock( Tests::class );
		$tests->shouldReceive( 'get_cookies' )->andReturn( $cookies );
		$tests->shouldReceive( 'has_rejected_cookie' )->andReturn( false );
		$tests->shouldReceive( 'get_clean_request_uri' )->andReturn( '/hello/' );

		$cache = new Cache( $tests, $config_mock, [ 'cache_dir_path' => '/tmp/cache' ] );

		return $cache->get_cache_path();
	}

	/**
	 * A cookie that arrived without the parts it was declared by holds their place in the name.
	 *
	 * @return void
	 */
	public function testShouldKeepThePlaceOfAPartThatDidNotArrive() {
		$path = $this->path_for(
			[ 'cache_dynamic_cookies' => [ 'prefs' => [ 'currency' ] ] ],
			[ 'prefs' => [ 'lang' => 'fr' ] ]
		);

		$this->assertStringEndsWith( '/index-.html', $path );
	}

	/**
	 * The same when it arrived as a plain value, which is not a part of anything.
	 *
	 * @return void
	 */
	public function testShouldKeepThePlaceOfPartsOfACookieSentAsAPlainValue() {
		$path = $this->path_for(
			[ 'cache_dynamic_cookies' => [ 'prefs' => [ 'currency' ] ] ],
			[ 'prefs' => 'eur' ]
		);

		$this->assertStringEndsWith( '/index-.html', $path );
	}

	/**
	 * A page named by the part itself is not the page named by its absence.
	 *
	 * @return void
	 */
	public function testShouldKeepAnArrivedPartApartFromAMissingOne() {
		$config = [ 'cache_dynamic_cookies' => [ 'prefs' => [ 'currency' ] ] ];

		$this->assertNotSame(
			$this->path_for( $config, [ 'prefs' => [ 'currency' => 'eur' ] ] ),
			$this->path_for( $config, [ 'prefs' => 'eur' ] )
		);
	}

	/**
	 * Every declared part of a cookie that arrived without them holds its place, one for one.
	 *
	 * @return void
	 */
	public function testShouldKeepThePlaceOfEveryPartOfACookieSentAsAPlainValue() {
		$path = $this->path_for(
			[ 'cache_dynamic_cookies' => [ 'prefs' => [ 'currency', 'lang' ] ] ],
			[ 'prefs' => 'eur' ]
		);

		$this->assertStringEndsWith( '/index--.html', $path );
	}

	/**
	 * A part named by digits holds its place too, rather than taking a letter of the plain value.
	 *
	 * @return void
	 */
	public function testShouldKeepThePlaceOfAPartNamedByDigits() {
		$path = $this->path_for(
			[ 'cache_dynamic_cookies' => [ 'prefs' => [ '0' ] ] ],
			[ 'prefs' => 'eur' ]
		);

		$this->assertStringEndsWith( '/index-.html', $path );
	}

	/**
	 * A part that arrived empty leaves no mark, as it always has, so its place is not held.
	 *
	 * This is what the plugin does today, not what it should do: two states of the same cookie share
	 * a name. Changing it renames files on every site that has one, so it is left as it is.
	 *
	 * @return void
	 */
	public function testShouldLeaveNoMarkForAPartThatArrivedEmpty() {
		$config = [ 'cache_dynamic_cookies' => [ 'prefs' => [ 'a', 'b' ] ] ];

		$this->assertSame(
			$this->path_for(
				$config,
				[
					'prefs' => [
						'a' => '',
						'b' => 'x',
					],
				]
				),
			$this->path_for(
				$config,
				[
					'prefs' => [
						'a' => 'x',
						'b' => '',
					],
				]
				)
		);
	}

	/**
	 * A declared cookie that did not arrive at all leaves no mark, the way a plain name never has.
	 *
	 * Also today's behaviour rather than a requirement: a name says which values arrived, not which
	 * cookies were declared, so two requests carrying different cookies can meet here.
	 *
	 * @return void
	 */
	public function testShouldLeaveNoMarkForACookieThatDidNotArrive() {
		$path = $this->path_for(
			[
				'cache_dynamic_cookies' => [
					'geo'   => [ 'country' ],
					'prefs' => [ 'currency' ],
				],
			],
			[ 'geo' => [ 'country' => 'fr' ] ]
		);

		$this->assertStringEndsWith( '/index-fr.html', $path );
	}

	/**
	 * A cookie written into the environment after this call was given its own does not name the file.
	 *
	 * @return void
	 */
	public function testShouldIgnoreACookieAddedToTheEnvironmentLater() {
		$config = [ 'cache_dynamic_cookies' => [ 'prefs' => [ 'currency' ] ] ];

		$this->assertSame(
			$this->path_for( $config, [] ),
			$this->path_for( $config, [], [ 'prefs' => [ 'currency' => 'eur' ] ] )
		);
	}

	/**
	 * One taken out of the environment afterwards still does.
	 *
	 * @return void
	 */
	public function testShouldStillNameTheFileFromACookieTakenOutOfTheEnvironment() {
		$config = [ 'cache_dynamic_cookies' => [ 'prefs' => [ 'currency' ] ] ];

		$this->assertSame(
			$this->path_for( $config, [ 'prefs' => [ 'currency' => 'eur' ] ] ),
			$this->path_for( $config, [ 'prefs' => [ 'currency' => 'eur' ] ], [] )
		);
	}

	/**
	 * The same cookie declared both ways names the file twice: by its value, then by its missing part.
	 *
	 * @return void
	 */
	public function testShouldNameTheFileFromACookieDeclaredBothWays() {
		$path = $this->path_for(
			[
				'cache_dynamic_cookies' => [
					0      => 'gdpr',
					'gdpr' => [ 'allowed_cookies' ],
				],
			],
			[ 'gdpr' => 'yes' ]
		);

		$this->assertStringEndsWith( '/index-yes-.html', $path );
	}

	/**
	 * The list this release stopped truncating: both cookies name the file.
	 *
	 * @return void
	 */
	public function testShouldNameTheFileFromEveryDeclaredCookie() {
		$path = $this->path_for(
			[
				'cache_dynamic_cookies' => [
					'geo'   => [ 'country' ],
					'prefs' => [ 'currency' ],
				],
			],
			[
				'geo'   => [ 'country' => 'fr' ],
				'prefs' => [ 'currency' => 'eur' ],
			]
		);

		$this->assertStringEndsWith( '/index-fr-eur.html', $path );
	}

	/**
	 * The same cookie sent as parts still names the file.
	 *
	 * @return void
	 */
	public function testShouldNameTheFileFromThePartsThatArrived() {
		$path = $this->path_for(
			[ 'cache_dynamic_cookies' => [ 'prefs' => [ 'currency' ] ] ],
			[ 'prefs' => [ 'currency' => 'eur' ] ]
		);

		$this->assertStringEndsWith( '/index-eur.html', $path );
	}
}
