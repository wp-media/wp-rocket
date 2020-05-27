<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Filters;
use WP_Rocket\Tests\Unit\FilesystemTestCase;


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

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnValidConfigFileName( $config, $expected ) {
		$this->config_file_name = isset($config['config_file_name']) ? $config['config_file_name'] : null;
		$filter = Filters\expectApplied('rocket_wp_config_name')->once();
		if( ! is_null( $this->config_file_name ) ){
			$filter->andReturn( $this->config_file_name );
		}

		$actual = rocket_find_wpconfig_path();
		if(false !== $actual){
			$actual = $this->filesystem->getUrl( $actual );
		}
		$this->assertEquals( $expected, $actual );
	}

	public function changeWpconfigFileName( $config_original_file_name ) {
		var_dump($this->config_file_name);
		return $this->config_file_name;
	}

}
