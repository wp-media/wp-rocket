<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\AssetsLocalCache;

use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\AssetsLocalCache::get_content
 * @group  Optimize
 * @group  AssetsLocalCache
 */
class Test_GetContent extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/AssetsLocalCache/getContent.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldSaveLocalContent( $url, $file, $content ) {
		$local_cache = new AssetsLocalCache( $this->filesystem->getUrl( 'wp-content/cache/min/' ), $this->filesystem );

		$this->assertSame(
			$this->format_the_html( $content ),
			$this->format_the_html( $local_cache->get_content( $url ) )
		);

		$this->assertTrue( $this->filesystem->exists( $file ) );
	}
}
