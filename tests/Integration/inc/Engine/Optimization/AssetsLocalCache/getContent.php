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

	private $content = [];

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldSaveLocalContent( $config, $expected ) {
		$local_cache = new AssetsLocalCache( $this->filesystem->getUrl( 'wp-content/cache/min/' ), $this->filesystem );

		if ( ! $config['found'] ) {
			$this->content [$config['url'] ] = [
				'body' => $expected
			];
		}

		add_filter( 'pre_http_request', [ $this, 'bypass_request'], 10, 3 );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $local_cache->get_content( $config['url'] ) )
		);

		$this->assertTrue( $this->filesystem->exists( $config['file'] ) );
	}

	public function bypass_request( $content, $parsed_args, $url ) {
		if ( ! isset( $this->content[ $url ] ) ) {
			return $content;
		}

		return $this->content[ $url ];
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'bypass_request' ], 10 );

		parent::tear_down();
	}
}
