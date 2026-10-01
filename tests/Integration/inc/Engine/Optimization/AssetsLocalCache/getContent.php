<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\AssetsLocalCache;

use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\AssetsLocalCache::get_content
 * @group  Optimize
 * @group  AssetsLocalCache
 */
class Test_GetContent extends FilesystemTestCase {
	use HttpRequestTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/AssetsLocalCache/getContent.php';

	public function set_up() {
		parent::set_up();

		$this->setup_http();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldSaveLocalContent( $config, $expected ) {
		$local_cache = new AssetsLocalCache( $this->filesystem->getUrl( 'wp-content/cache/min/' ), $this->filesystem );

		// A content already cached locally must not be fetched again.
		$this->config['http'] = $config['found'] ? [] : [
			$config['url'] => [
				'body'     => $expected,
				'response' => [ 'code' => 200, 'message' => 'OK' ],
			],
		];

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $local_cache->get_content( $config['url'] ) )
		);

		$this->assertTrue( $this->filesystem->exists( $config['file'] ) );
	}

	public function tear_down() {
		$this->tear_down_http();

		parent::tear_down();
	}
}
