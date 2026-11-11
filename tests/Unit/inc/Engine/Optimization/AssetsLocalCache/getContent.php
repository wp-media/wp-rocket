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
				->with( $config['url'] )
				->andReturn( $config['response'] );

			Functions\when( 'wp_remote_retrieve_response_code' )->alias(
				function ( $response ) {
					return $response['code'];
				}
			);
			Functions\when( 'wp_remote_retrieve_header' )->alias(
				function ( $response, $header ) {
					return $response['headers'][ $header ] ?? '';
				}
			);
			Functions\when( 'wp_remote_retrieve_body' )->alias(
				function ( $response ) {
					return $response['body'];
				}
			);
		}

		$local_cache = new AssetsLocalCache(
			$this->filesystem->getUrl( 'wp-content/cache/min/' ),
			$this->filesystem,
			$config['reject_html']
		);

		$this->assertSame(
			$expected,
			$local_cache->get_content( $config['url'] )
		);

		$this->assertSame( $config['file_written'], $this->filesystem->exists( $config['file'] ) );
	}
}
