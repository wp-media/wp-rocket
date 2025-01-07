<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\AssetsLocalCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\AssetsLocalCache::get_content
 * @group  Optimize
 * @group  AssetsLocalCache
 */
class Test_GetContent extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/AssetsLocalCache/getContent.php';

	public function setUp() : void {
		parent::setUp();

		$this->stubWpParseUrl();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldSaveLocalContent( $config, $expected ) {
		if ( ! $config['found'] ) {
			Functions\expect( 'wp_remote_get' )
				->once()
				->with( $config['url'] );

			Functions\expect( 'wp_remote_retrieve_body' )
				->once()
				->andReturn( $expected );
		}


		$local_cache = new AssetsLocalCache( $this->filesystem->getUrl( 'wp-content/cache/min/' ), $this->filesystem );

		$this->assertSame(
			$expected,
			$local_cache->get_content( $config['url'] )
		);

		$this->assertTrue( $this->filesystem->exists( $config['file'] ) );
	}
}
