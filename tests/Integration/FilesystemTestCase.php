<?php

namespace WP_Rocket\Tests\Integration;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\ArrayTrait;
use WPMedia\PHPUnit\VirtualFilesystemDirect;
use WPMedia\PHPUnit\Integration\TestCase;

abstract class FilesystemTestCase extends TestCase {

	use ArrayTrait;

	/**
	 * Path to the config and test data in the Fixtures directory.
	 * Set this path in each test class.
	 *
	 * @var string
	 */
	protected $path_to_test_data;

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
	protected $config = [];
	
	/**
	 * Virtual filestructure for this test, i.e. default merged with configured.
	 *
	 * @var array
	 */
	private $merged_structure;
	
	/**
	 * Original virtual files with flattened full paths.
	 *
	 * @var array
	 */
	protected $original_files = [];
	
	/**
	 * Original virtual directories with flattened full paths.
	 *
	 * @var array
	 */
	protected $original_dirs = [];

	/**
	 * Prepares the test environment before each test.
	 */
	public function setUp() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		$vfs                  = ArrayTrait::get( $this->config['structure'], rtrim( $this->config['vfs_dir'], '\//' ), [], '/' );
		$this->original_files = static::getAllFiles( $vfs, $this->config['vfs_dir'] );
		$this->original_dirs  = static::getAllDirs( $vfs, $this->config['vfs_dir'] );

		$this->filesystem     = new VirtualFilesystemDirect( 'wp-content', $this->mergeStructure(), 0777 );
		$this->rootVirtualUrl = $this->filesystem->getUrl( '/' );

		parent::setUp();

		// Redefine rocket_direct_filesystem() to use the virtual filesystem.
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
	}

	public function addDataProvider() {
		$this->loadConfig();

		return $this->config['test_data'];
	}

	protected function loadConfig() {
		$this->config = require WP_ROCKET_TESTS_FIXTURES_DIR . $this->path_to_test_data;
	}

	protected function mergeStructure() {
		if ( ! empty( $this->merged_structure ) ) {
			return $this->merged_structure;
		}

		if ( isset( $this->config['structure'] ) ) {
			$this->structure = $this->config['structure'];
		}
		$this->merged_structure = array_replace_recursive( $this->getDefaultVfs(), $this->config['structure'] );

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
	 * @param string  $dir      Virtual directory absolute path.
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

	public static function getAllFiles( array $dir, $prepend ) {
		return array_keys( ArrayTrait::flatten( $dir, $prepend ) );
	}

	public static function getAllDirs( array $dir, $prepend ) {
		return array_keys( ArrayTrait::flatten( $dir, $prepend, true ) );
	}
}
