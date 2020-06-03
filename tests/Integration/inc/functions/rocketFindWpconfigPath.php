<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;


/**
 * @covers ::rocket_find_wpconfig_path
 * @group Functions
 */
class Test_RocketFindWpconfigPath extends FilesystemTestCase {
	protected $path_to_test_data   = '/inc/functions/rocketFindWpconfigPath.php';
	/**
	 * @var string|null
	 */
	private $config_file_name = null;

	public function setUp()
	{
		parent::setUp();

		$this->abspath = $this->filesystem->getUrl( $this->config['vfs_dir'] );
	}

	public function tearDown()
	{
		if( !is_null( $this->config_file_name ) ){
			remove_filter('rocket_wp_config_name', [$this, 'changeWpconfigFileName']);
		}

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnValidConfigFileName( $config, $expected ) {
		$this->config_file_name = isset($config['config_file_name']) ? $config['config_file_name'] : null;

		if( !is_null( $this->config_file_name ) ){
			add_filter('rocket_wp_config_name', [$this, 'changeWpconfigFileName']);
		}

		$actual = rocket_find_wpconfig_path();
		if(false !== $actual){
			$actual = $this->filesystem->getUrl( $actual );
		}
		$this->assertEquals( $expected, $actual );
	}

	public function changeWpconfigFileName( $config_original_file_name ) {
		return $this->config_file_name;
	}

}
