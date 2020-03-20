<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\BeaverBuilder;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder::purge_cache
 * @group BeaverBuilder
 * @group ThirdParty
 */
class Test_PurgeCache extends FilesystemTestCase {
	protected $structure = [
		'min'          => [
			'1' => [
				'5c795b0e3a1884eec34a989485f863ff.js'     => '',
				'fa2965d41f1515951de523cecb81f85e.css'    => '',
			],
		],
		'wp-rocket'    => [
			'example.org' => [
				'index.html'      => '',
				'index.html_gzip' => '',
			],
			'example.org-Greg-594d03f6ae698691165999' => [
				'about' => [
					'index.html'      => '',
					'index.html_gzip' => '',
				],
			],
		],
	];

	public function testShouldCleanRocketCacheDirectoriesWhenSaveLayout() {
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'min/' ) )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'wp-rocket/' ) );

		do_action( 'fl_builder_before_save_layout' );

		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertNull( $this->filesystem->getFile( 'min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
	}

	public function testShouldCleanRocketCacheDirectoriesWhenFLCacheCleared() {
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'min/' ) )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'wp-rocket/' ) );

		do_action( 'fl_builder_cache_cleared' );

		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html' ) );
		$this->assertNull( $this->filesystem->getFile( 'wp-rocket/example.org-Greg-594d03f6ae698691165999/about/index.html_gzip' ) );
		$this->assertNull( $this->filesystem->getFile( 'min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertNull( $this->filesystem->getFile( 'min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
	}
}
