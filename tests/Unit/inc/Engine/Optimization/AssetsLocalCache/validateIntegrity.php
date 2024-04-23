<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\AssetsLocalCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\AssetsLocalCache::validate_integrity
 * @group  Optimize
 * @group  AssetsLocalCache
 */
class Test_ValidateIntegrity extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/AssetsLocalCache/validateIntegrity.php';

	public function setUp() : void {
		parent::setUp();

		$this->stubWpParseUrl();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoexpected( $config, $expected ) {
		$asset = $config['asset'];
		$file_contents = isset( $config['file_contents'] ) ? $config['file_contents'] : null;

		if ( ! is_null( $file_contents ) ){
			Functions\expect( 'wp_remote_get' )
				->once()
				->with( $asset['url'] );

			Functions\expect( 'wp_remote_retrieve_body' )
				->once()
				->andReturn( $file_contents );
		}

		$local_cache = new AssetsLocalCache( $this->filesystem->getUrl( 'wp-content/cache/min/' ), $this->filesystem );

		$this->assertSame( $expected, $local_cache->validate_integrity( $asset ) );
	}
}
