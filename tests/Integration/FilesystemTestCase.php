<?php

namespace WP_Rocket\Tests\Integration;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	protected $structure = [
		'busting'      => [
			'1' => [
				'wp-content' => [
					'themes' => [
						'storefront' => [
							'assets'             => [
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
			],
		],
		'critical-css' => [],
		'min'          => [
			'1' => [
				'5c795b0e3a1884eec34a989485f863ff.js'     => '',
				'5c795b0e3a1884eec34a989485f863ff.js.gz'  => '',
				'fa2965d41f1515951de523cecb81f85e.css'    => '',
				'fa2965d41f1515951de523cecb81f85e.css.gz' => '',
			],
		],
		'wp-rocket'    => [
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
	];

	public function setUp() {
		parent::setUp();

		// Redefine rocket_direct_filesystem() to use the virtual filesystem.
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
	}

	/**
	 * Gets the files and directories for the given virtual root directory.
	 *
	 * @param string $dir Virtual directory absolute path.
	 *
	 * @return array Array of files and directories in the given root directory.
	 */
	protected function scandir( $dir ) {
		$items = @scandir( $dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Valid use case.

		if ( ! $items ) {
			return [];
		}

		// Get rid of dot files when present.
		if ( '.' === $items[0] ) {
			unset( $items[0], $items[1] );
		}

		$dir = trailingslashit( $dir );

		return array_map(
			function ( $item ) use ( $dir ) {
				return "{$dir}{$item}";
			},
			$items
		);
	}

	/**
	 * Recursively deletes all the files in the given virtual directory.
	 *
	 * @param string $dir Virtual directory absolute path.
	 */
	protected function delete_files( $dir ) {
		foreach ( $this->scandir( $dir ) as $item ) {
			if ( $this->filesystem->is_dir( $item ) ) {
				$this->delete_files( $item );
			} else {
				$this->filesystem->delete( $item );
			}
		}
	}
}
