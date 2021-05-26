<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\WPCache;

use Brain\Monkey\Filters;
use ReflectionMethod;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @uses   ::rocket_get_constant
 *
 * @group  WPCache
 */
class Test_FindWpConfigPath extends FileSystemTestCase {
    protected $path_to_test_data = '/inc/Engine/Cache/WPCache/findWpconfigPath.php';

	/**
	 * @var string|null
	 */
	private $config_file_name = null;

	public function setUp() : void {
		parent::setUp();

		$this->abspath = $this->filesystem->getUrl( $this->config['vfs_dir'] );
    }

    /**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnValidConfigFileName( $config, $expected ) {
		$this->config_file_name = isset( $config['config_file_name'] ) ? $config['config_file_name'] : null;

        $filter = Filters\expectApplied('rocket_wp_config_name')->once();

		if ( ! is_null( $this->config_file_name ) ) {
			$filter->andReturn( $this->config_file_name );
        }

		$find_wpconfig_path = new ReflectionMethod( 'WP_Rocket\Engine\Cache\WPCache', 'find_wpconfig_path' );
		$find_wpconfig_path->setAccessible( true );

        $actual = $find_wpconfig_path->invoke( new WPCache( $this->filesystem ) );

		if ( false !== $actual ) {
			$actual = $this->filesystem->getUrl( $actual );
        }

		$this->assertEquals( $expected, $actual );
	}

	public function changeWpconfigFileName( $config_original_file_name ) {
		return $this->config_file_name;
	}
}
