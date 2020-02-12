<?php

namespace WP_Rocket\Tests\Unit;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	protected $structure = [
		'wp-rocket' => [
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
		],
		'min' => [
			'1' => [
				'5c795b0e3a1884eec34a989485f863ff.js'     => '',
				'5c795b0e3a1884eec34a989485f863ff.js.gz'  => '',
				'fa2965d41f1515951de523cecb81f85e.css'    => '',
				'fa2965d41f1515951de523cecb81f85e.css.gz' => '',
			],
		],
		'busting' => [
			'1' => [
				'wp-content' => [
					'themes' => [
						'storefront' => [
							'assets' => [
								'js' => [
									'navigation.min-2.5.3.js'    => '',
									'navigation.min-2.5.3.js.gz' => '',
								],
							],
							'style-2.5.3.css'    => '',
							'style-2.5.3.css.gz' => '',
						],
					],
				],
			]
		]
	];

	public function setUp() {
		parent::setUp();

		// Redefine rocket_direct_filesystem() to use the virtual filesystem.
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
	}
}
