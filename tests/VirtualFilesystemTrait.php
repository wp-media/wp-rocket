<?php

namespace WP_Rocket\Tests;

use Brain\Monkey\Functions;
use org\bovigo\vfs\vfsStream;

trait VirtualFilesystemTrait {
	protected $original_entries = [];
	protected $shouldNotClean   = [];
	protected $entriesBefore    = [];
	protected $dumpResults      = false;

	protected function initDefaultStructure() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		if ( array_key_exists( 'structure', $this->config ) ) {
			return;
		}

		$this->config['structure'] = require WP_ROCKET_TESTS_FIXTURES_DIR . '/vfs-structure/default.php';
	}

	protected function redefineRocketDirectFilesystem() {
		// Redefine rocket_direct_filesystem() to use the virtual filesystem.
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
	}

	protected function setUpOriginalEntries() {
		$this->original_entries = array_merge( $this->original_files, $this->original_dirs );
		$this->original_entries = array_filter( $this->original_entries );
		sort( $this->original_entries );
	}

	protected function stripVfsRoot( $path ) {
		$search = vfsStream::SCHEME . "://{$this->rootVirtualDir}";
		$search = rtrim( $search, '/\\' ) . '/';

		return str_replace( $search, '', $path );
	}

	protected function getEntriesBefore( $dir = '' ) {
		if ( '' === $dir ) {
			$dir = $this->filesystem->getUrl( $this->config['vfs_dir'] );
		}

		$this->entriesBefore = $this->filesystem->getListing( $dir );
	}

	protected function getShouldNotCleanEntries( array $shouldNotClean ) {
		$this->shouldNotClean = [];
		foreach ( $shouldNotClean as $entry => $scanDir ) {
			$this->shouldNotClean[] = $entry;
			if ( $scanDir && $this->filesystem->is_dir( $entry ) ) {
				$this->shouldNotClean = array_merge( $this->shouldNotClean, $this->filesystem->getListing( $entry ) );
			}
		}
	}

	protected function generateEntriesShouldExistAfter( array $shouldClean, $dir = '' ) {
		$this->getEntriesBefore( $dir );

		$cleaned = [];
		foreach ( $shouldClean as $entry => $contents ) {
			if ( ! $this->filesystem->is_dir( $entry ) ) {
				$cleaned[] = $entry;
				continue;
			}

			// Directory should be deleted.
			if ( is_null( $contents ) ) {
				$cleaned[] = $entry;
			}

			$cleaned = array_merge( $cleaned, $this->filesystem->getListing( $entry ) );
		}

		$this->shouldNotClean = array_values( array_diff( $this->entriesBefore, $cleaned ) );
	}

	protected function checkEntriesDeleted( array $shouldClean ) {
		foreach ( $shouldClean as $entry => $contents ) {
			// Deleted.
			if ( empty( $contents ) ) {
				if ( $this->dumpResults && false !== $this->filesystem->exists( $entry ) ) {
					echo "\n Entry: {$entry} \n";
					if ( $this->filesystem->is_dir( $entry ) ) {
						var_dump( $this->filesystem->getFilesListing( $entry ) );
					}
				}
				$this->assertFalse( $this->filesystem->exists( $entry ) );
			} else {
				// Emptied, but not deleted.
				$entries = $this->filesystem->getFilesListing( $entry );
				if ( $this->dumpResults ) {
					var_dump( $entries );
				}
				$this->assertSame( $contents, $entries );
			}
		}
	}

	protected function checkShouldNotDeleteEntries( $dir = '' ) {
		if ( '' === $dir ) {
			$dir = $this->filesystem->getUrl( $this->config['vfs_dir'] );
		}

		$entriesAfterCleaning = $this->filesystem->getListing( $dir );
		$actual               = array_diff( $entriesAfterCleaning, $this->shouldNotClean );
		if ( $this->dumpResults ) {
			var_dump( $actual );
		}
		$this->assertEmpty( $actual );
	}

	protected function whenRocketGetConstant() {
		Functions\when( 'rocket_get_constant' )->alias(
			function ( $constant_name, $default = null ) {
				return $this->getConstant( $constant_name, $default );
			}
		);
	}

	protected function getConstant( $constant_name, $default = null ) {
		switch ( $constant_name ) {
			case 'ABSPATH':
				return 'vfs://public/';

			case 'FS_CHMOD_DIR':
				return 0777;

			case 'FS_CHMOD_FILE':
				return 0666;

			case 'WP_CONTENT_DIR':
				return 'vfs://public/wp-content';

			case 'WP_ROCKET_CONFIG_PATH':
				return 'vfs://public/wp-content/wp-rocket-config/';

			case 'WP_ROCKET_INC_PATH':
				return 'vfs://public/wp-content/plugins/wp-rocket/inc/';

			case 'WP_ROCKET_PATH':
				return 'vfs://public/wp-content/plugins/wp-rocket/';

			case 'WP_ROCKET_PHP_VERSION':
				return '5.6';

			case 'WP_ROCKET_VENDORS_PATH':
				return 'vfs://public/wp-content/plugins/wp-rocket/inc/vendors/';

			default:
				if ( ! rocket_has_constant( $constant_name ) ) {
					return $default;
				}

				return constant( $constant_name );
		}
	}

	public function getPathToFixturesDir() {
		return WP_ROCKET_TESTS_FIXTURES_DIR;
	}

	public function getDefaultVfs() {
		return [
			'wp-admin'      => [],
			'wp-content'    => [
				'cache'            => [
					'busting'      => [
						1 => [],
					],
					'critical-css' => [],
					'min'          => [],
					'wp-rocket'    => [
						'index.html' => '',
					],
				],
				'mu-plugins'       => [],
				'plugins'          => [
					'wp-rocket' => [],
				],
				'themes'           => [
					'twentytwenty' => [],
				],
				'uploads'          => [],
				'wp-rocket-config' => [],
			],
			'wp-includes'   => [],
			'wp-config.php' => '',
			'index.php'     => '',
		];
	}
}
