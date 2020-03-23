<?php

namespace WP_Rocket\Tests\Integration;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\VirtualFilesystemDirect;
use WPMedia\PHPUnit\Integration\TestCase;

abstract class FilesystemTestCase extends TestCase {
	protected static $path_to_test_data;

	/**
	 * Overwrite with the structure for this test. Gets merged with the default structure.
	 *
	 * @var array
	 */
	protected $structure = [];

	/**
	 * Instance of the virtual filesystem.
	 *
	 * @var VirtualFilesystemDirect
	 */
	protected $filesystem;

	/**
	 * URL to the root directory of the virtual filesystem.
	 *
	 * @var string
	 */
	protected $rootVirtualUrl;

	/**
	 * Structure + test data configuration.
	 *
	 * @var array
	 */
	protected static $config;

	private $merged_structure;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		if ( empty( static::$config ) ) {
			static::loadConfig();
		}
	}

	/**
	 * Prepares the test environment before each test.
	 */
	public function setUp() {
		$this->filesystem     = new VirtualFilesystemDirect( 'wp-content', $this->mergeStructure(), 0777 );
		$this->rootVirtualUrl = $this->filesystem->getUrl( '/' );

		parent::setUp();

		// Redefine rocket_direct_filesystem() to use the virtual filesystem.
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
	}

	public function addDataProvider() {
		if ( empty( static::$config ) ) {
			static::loadConfig();
		}

		return static::$config['test_data'];
	}

	protected static function loadConfig() {
		static::$config = require WP_ROCKET_TESTS_FIXTURES_DIR . static::$path_to_test_data;
	}

	protected function mergeStructure() {
		if ( ! empty( $this->merged_structure ) ) {
			return $this->merged_structure;
		}

		if ( isset( static::$config['structure'] ) ) {
			$this->structure = static::$config['structure'];
		}
		$this->merged_structure = array_replace_recursive( $this->getDefaultVfs(), static::$config['structure'] );

		return $this->merged_structure;
	}

	/**
	 * Gets the default virtual wp-content directory filesystem structure.
	 *
	 * @return array
	 */
	private function getDefaultVfs() {
		return [
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
			'plugins'          => [],
			'themes'           => [],
			'uploads'          => [],
			'wp-rocket-config' => [],
		];
	}

	/**
	 * Gets the files and directories for the given virtual root directory.
	 *
	 * @param string $dir Virtual directory absolute path.
	 * @param boolean $relative Optional. When true, returns as relative path.
	 *
	 * @return array Array of files and directories in the given root directory.
	 */
	public function scandir( $dir, $relative = false ) {
		$items = @scandir( $this->filesystem->getUrl( $dir ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Valid use case.

		if ( ! $items ) {
			return [];
		}

		// Get rid of dot files when present.
		if ( '.' === $items[0] ) {
			unset( $items[0], $items[1] );

			// Reindex back to 0.
			$items = array_values( $items );
		}

		if ( $relative ) {
			return $items;
		}

		$dir = trailingslashit( $dir );

		return array_map(
			function ( $item ) use ( $dir ) {
				return "{$dir}{$item}";
			},
			$items
		);
	}
}
