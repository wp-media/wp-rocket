<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\AssetsLocalCache;

use WP_Error;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\AssetsLocalCache::get_content
 * @group  Optimize
 * @group  AssetsLocalCache
 */
class Test_GetContent extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/AssetsLocalCache/getContent.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldSaveLocalContent( $config, $expected ) {
		$local_cache = new AssetsLocalCache(
			$this->filesystem->getUrl( 'wp-content/cache/min/' ),
			$this->filesystem,
			$config['reject_html']
		);

		// A content already cached locally must not be fetched again.
		$this->config['http'] = $config['found'] ? [] : [
			$config['url'] => $this->get_mocked_response( $config['response'] ),
		];

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $local_cache->get_content( $config['url'] ) )
		);

		$this->assertSame( $config['file_written'], $this->filesystem->exists( $config['file'] ) );
	}

	private function get_mocked_response( array $response ) {
		if ( '' === $response['code'] ) {
			return new WP_Error( 'http_request_failed', 'Request failed' );
		}

		return [
			'body'     => $response['body'],
			'headers'  => $response['headers'],
			'response' => [ 'code' => $response['code'] ],
		];
	}
}
