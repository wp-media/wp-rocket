<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Abilities\PurgeCache;

use Brain\Monkey\Functions;
use Mockery;
use stdClass;
use WP_Post;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Abilities\PurgeCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Preload\Abilities\PurgeCache::execute()
 *
 * @group Preload
 * @group Abilities
 */
class ExecuteTest extends TestCase {

	/**
	 * Fully qualified name of `rocket_clean_post()` as resolved from PurgeCache's namespace.
	 *
	 * `rocket_clean_post()` is declared for real in inc/common/purge.php, which is not
	 * preloaded by tests/Unit/bootstrap.php (unlike inc/functions/files.php, which already
	 * declares rocket_clean_domain()/rocket_clean_files()/rocket_clean_home() for real).
	 * Brain\Monkey only evals a throwaway global stub for a mocked function when that
	 * function does not already exist anywhere; mocking the unqualified global name here
	 * would therefore permanently declare a global `rocket_clean_post()` stub for the rest
	 * of the test run, which then fatals ("Cannot redeclare") the moment any other test
	 * lazily `require`s the real inc/common/purge.php. Mocking the namespace-qualified name
	 * instead relies on PHP's standard unqualified-function-call fallback (current namespace
	 * first, then global), so PurgeCache::clear_post_scope()'s unqualified call to
	 * `rocket_clean_post()` resolves to this namespaced mock without ever touching, or
	 * conflicting with, the real global function.
	 *
	 * @var string
	 */
	private const ROCKET_CLEAN_POST = 'WP_Rocket\Engine\Preload\Abilities\rocket_clean_post';

	/**
	 * Options_Data mock.
	 *
	 * @var Options_Data|Mockery\MockInterface
	 */
	private $options;

	/**
	 * Ability instance under test.
	 *
	 * @var PurgeCache
	 */
	private $ability;

	protected function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->ability = new PurgeCache( $this->options );

		$this->stubTranslationFunctions();

		Functions\when( 'home_url' )->justReturn( 'https://example.com' );

		Functions\when( 'untrailingslashit' )->alias(
			function ( $string ) {
				return rtrim( $string, '/\\' );
			}
		);
	}

	private function mockPost( int $id ) {
		$post              = Mockery::mock( WP_Post::class );
		$post->shouldIgnoreMissing();
		return $post;
	}

	public function testShouldRejectMissingScope(): void {
		Functions\expect( 'rocket_clean_files' )->never();
		Functions\expect( self::ROCKET_CLEAN_POST )->never();
		Functions\expect( 'rocket_clean_domain' )->never();

		$result = $this->ability->execute( [] );

		$this->assertFalse( $result['accepted'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldRejectInvalidScope(): void {
		$result = $this->ability->execute( [ 'scope' => 'term' ] );

		$this->assertFalse( $result['accepted'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldRejectUrlScopeWithoutUrl(): void {
		$result = $this->ability->execute( [ 'scope' => 'url' ] );

		$this->assertFalse( $result['accepted'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldRejectUrlOnDifferentHost(): void {
		Functions\expect( 'rocket_clean_files' )->never();

		$this->options->shouldReceive( 'get' )->with( 'do_cloudflare', 0 )->andReturn( 0 );

		$result = $this->ability->execute(
			[
				'scope' => 'url',
				'url'   => 'https://not-this-site.com/page',
			]
		);

		$this->assertFalse( $result['accepted'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldRejectUrlOutsideSubdirectorySitePath(): void {
		Functions\when( 'home_url' )->justReturn( 'https://example.com/site2' );
		Functions\expect( 'rocket_clean_files' )->never();

		$result = $this->ability->execute(
			[
				'scope' => 'url',
				// Same host, but belongs to a sibling subsite (different path prefix).
				'url'   => 'https://example.com/site1/page',
			]
		);

		$this->assertFalse( $result['accepted'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldRejectUrlWithHostThatIsStringPrefixOfHomeHost(): void {
		Functions\expect( 'rocket_clean_files' )->never();

		// "example.com" is a literal string-prefix of "example.com.attacker.net",
		// but the two are entirely different domains and must not match.
		$result = $this->ability->execute(
			[
				'scope' => 'url',
				'url'   => 'https://example.com.attacker.net/page',
			]
		);

		$this->assertFalse( $result['accepted'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldRejectSiblingSubdirectorySiteWithNumericPrefixCollision(): void {
		Functions\when( 'home_url' )->justReturn( 'https://example.com/site1' );
		Functions\expect( 'rocket_clean_files' )->never();

		// "/site1" is a literal string-prefix of "/site10", but they are different
		// subsites on a subdirectory multisite install and must not match.
		$result = $this->ability->execute(
			[
				'scope' => 'url',
				'url'   => 'https://example.com/site10/page',
			]
		);

		$this->assertFalse( $result['accepted'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldClearSingleUrlInScope(): void {
		Functions\expect( 'rocket_clean_files' )
			->once()
			->with( [ 'https://example.com/page' ] );

		Functions\expect( 'rocket_clean_home' )->never();

		$this->options->shouldReceive( 'get' )->with( 'do_cloudflare', 0 )->andReturn( 0 );

		$result = $this->ability->execute(
			[
				'scope' => 'url',
				'url'   => 'https://example.com/page',
			]
		);

		$this->assertTrue( $result['accepted'] );
		$this->assertSame( 'url', $result['scope'] );
		$this->assertSame( [ 'https://example.com/page' ], $result['cleared_urls'] );
		$this->assertNull( $result['cloudflare_purge_triggered'] );
		$this->assertNull( $result['error'] );
	}

	public function testShouldClearHomepageUrlWithRocketCleanHome(): void {
		Functions\expect( 'rocket_clean_home' )->once();
		Functions\expect( 'rocket_clean_files' )->never();

		$this->options->shouldReceive( 'get' )->with( 'do_cloudflare', 0 )->andReturn( 0 );

		$result = $this->ability->execute(
			[
				'scope' => 'url',
				'url'   => 'https://example.com',
			]
		);

		$this->assertTrue( $result['accepted'] );
	}

	public function testShouldNotTriggerCloudflareForUrlScopeWhenCloudflareEnabled(): void {
		Functions\expect( 'rocket_clean_files' )->once();

		$this->options->shouldReceive( 'get' )->with( 'do_cloudflare', 0 )->andReturn( 1 );

		$result = $this->ability->execute(
			[
				'scope' => 'url',
				'url'   => 'https://example.com/page',
			]
		);

		$this->assertFalse( $result['cloudflare_purge_triggered'] );
	}

	public function testShouldRejectPostScopeWithoutPostId(): void {
		$result = $this->ability->execute( [ 'scope' => 'post' ] );

		$this->assertFalse( $result['accepted'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldRejectPostScopeWhenPostNotFound(): void {
		Functions\when( 'get_post' )->justReturn( null );
		Functions\expect( self::ROCKET_CLEAN_POST )->never();

		$result = $this->ability->execute(
			[
				'scope'   => 'post',
				'post_id' => 999,
			]
		);

		$this->assertFalse( $result['accepted'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldClearPostScope(): void {
		Functions\when( 'get_post' )->justReturn( $this->mockPost( 42 ) );
		Functions\when( 'get_permalink' )->justReturn( 'https://example.com/my-post' );

		Functions\expect( self::ROCKET_CLEAN_POST )
			->once()
			->with( 42 );

		$this->options->shouldReceive( 'get' )->with( 'do_cloudflare', 0 )->andReturn( 1 );

		$result = $this->ability->execute(
			[
				'scope'   => 'post',
				'post_id' => 42,
			]
		);

		$this->assertTrue( $result['accepted'] );
		$this->assertSame( 'post', $result['scope'] );
		$this->assertSame( [ 'https://example.com/my-post' ], $result['cleared_urls'] );
		$this->assertTrue( $result['cloudflare_purge_triggered'] );
	}

	public function testShouldNotTriggerCloudflareForPostScopeWhenCloudflareDisabled(): void {
		Functions\when( 'get_post' )->justReturn( $this->mockPost( 42 ) );
		Functions\when( 'get_permalink' )->justReturn( 'https://example.com/my-post' );
		Functions\expect( self::ROCKET_CLEAN_POST )->once();

		$this->options->shouldReceive( 'get' )->with( 'do_cloudflare', 0 )->andReturn( 0 );

		$result = $this->ability->execute(
			[
				'scope'   => 'post',
				'post_id' => 42,
			]
		);

		$this->assertNull( $result['cloudflare_purge_triggered'] );
	}

	public function testShouldClearDomainScope(): void {
		Functions\expect( 'rocket_clean_domain' )
			->once()
			->with( 'fr' );

		$this->options->shouldReceive( 'get' )->with( 'do_cloudflare', 0 )->andReturn( 1 );

		$result = $this->ability->execute(
			[
				'scope' => 'domain',
				'lang'  => 'fr',
			]
		);

		$this->assertTrue( $result['accepted'] );
		$this->assertSame( 'domain', $result['scope'] );
		$this->assertNull( $result['cleared_urls'] );
		$this->assertTrue( $result['cloudflare_purge_triggered'] );
	}

	public function testShouldClearDomainScopeWithDefaultEmptyLang(): void {
		Functions\expect( 'rocket_clean_domain' )
			->once()
			->with( '' );

		$this->options->shouldReceive( 'get' )->with( 'do_cloudflare', 0 )->andReturn( 0 );

		$result = $this->ability->execute( [ 'scope' => 'domain' ] );

		$this->assertTrue( $result['accepted'] );
		$this->assertNull( $result['cloudflare_purge_triggered'] );
	}

	public function testShouldSucceedEvenWhenNoProblemDetected(): void {
		// No precondition check exists: clearing must succeed unconditionally.
		Functions\expect( 'rocket_clean_domain' )->once();

		$this->options->shouldReceive( 'get' )->with( 'do_cloudflare', 0 )->andReturn( 0 );

		$result = $this->ability->execute( [ 'scope' => 'domain' ] );

		$this->assertTrue( $result['accepted'] );
	}
}
