<?php

namespace WP_Rocket\Tests\Integration;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	protected $structure = [
		'example.org'                             => [
			'index.html'      => '',
			'index.html_gzip' => '',
			'about'           => [
				'index.html'             => '',
				'index.html_gzip'        => '',
				'index-mobile.html'      => '',
				'index-mobile.html_gzip' => '',
			],
			'category'        => [
				'wordpress' => [
					'index.html'      => '',
					'index.html_gzip' => '',
				],
			],
			'blog'            => [
				'index.html'      => '',
				'index.html_gzip' => '',
			],
			'en'              => [
				'index.html'      => '',
				'index.html_gzip' => '',
			],
		],
		'example.org-Greg-594d03f6ae698691165999' => [
			'index.html'      => '',
			'index.html_gzip' => '',
		],
	];
	protected $cache_path;

	public function setUp() {
		$this->structure['wp-rocket'] = $this->wprocket_structure;
		parent::setUp();

		// Redefine rocket_direct_filesystem() to use the virtual filesystem.
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
		$this->cache_path = $this->filesystem->getUrl( 'wp-rocket' );
	}
}
