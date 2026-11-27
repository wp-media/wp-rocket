<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\Purge;

use Mockery;
use WP_Rocket\Engine\Cache\Purge;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\Purge::purge_url
 *
 * vfsStream decodes percent-encoding when resolving a path (and does so case-insensitively), so it
 * cannot hold a directory named with raw `%XX` sequences. That is exactly the on-disk layout this
 * bug is about, so the behaviour is asserted at the `exists()` seam: purge_url must look up the
 * lowercase-encoded path that matches the cache files written by Cache::reset_lowercase().
 *
 * @group purge_actions
 */
class TestPurgeUrl extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/Purge/purgeUrl.php';

	protected function setUp(): void {
		parent::setUp();

		// Reset the static cache of domain dirs so it resolves against this test's filesystem.
		_rocket_get_cache_dirs( '', '', true );
	}

	protected function tearDown(): void {
		_rocket_get_cache_dirs( '', '', true );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldLookUpLowercaseEncodedPath( $url, $expected_suffix ) {
		$this->stubWpParseUrl();

		$probed     = null;
		$filesystem = Mockery::mock();
		$filesystem->shouldReceive( 'exists' )
			->andReturnUsing(
				function ( $path ) use ( &$probed ) {
					$probed = $path;

					return false;
				}
			);

		$purge = new Purge( $filesystem, $this->createPartialMock( Cache::class, [ 'query' ] ) );
		$purge->purge_url( $url );

		$this->assertStringEndsWith( $expected_suffix, $probed );
	}
}
