<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\Cache\FilesystemCache;

use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Cache\FilesystemCache::set
 *
 * @group LazyloadCSS
 */
class Test_Set extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Common/Cache/FilesystemCache/integration/set.php';

	protected $config;

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldOnlyWriteInsideHostDir( $config, $expected ) {
		$cache  = new FilesystemCache( 'background-css/' );
		$before = $this->get_entries_outside_host_dir();

		$this->assertSame( $expected['output'], $cache->set( $config['key'], 'content' ) );
		$this->assertSame( $before, $this->get_entries_outside_host_dir() );

		if ( null !== $expected['path'] ) {
			$this->assertSame( 'content', $this->filesystem->get_contents( $this->filesystem->getUrl( $expected['path'] ) ) );
		}

		foreach ( $expected['absent'] as $path ) {
			$this->assertFalse( $this->filesystem->exists( $this->filesystem->getUrl( $path ) ) );
		}
	}

	/**
	 * Lists the vfs entries outside the blog 1 example.org directory, with file contents.
	 *
	 * @return array
	 */
	protected function get_entries_outside_host_dir(): array {
		$host_dir = $this->filesystem->getUrl( 'wp-content/cache/background-css/1/example.org/' );
		$entries  = [];

		foreach ( $this->filesystem->getListing( '' ) as $entry ) {
			if ( 0 === strpos( $entry, $host_dir ) ) {
				continue;
			}

			$entries[ $entry ] = $this->filesystem->is_dir( $entry ) ? null : $this->filesystem->get_contents( $entry );
		}

		return $entries;
	}
}
