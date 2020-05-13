<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\DataManager;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\DataManager::save_cpcss
 * @group CriticalPath
 * @group  vfs
 */
class Test_SaveCPCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/DataManager/saveCPCSS.php';

	public function setUp()
	{
		parent::setUp();

		$this->whenRocketGetConstant();
	}

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$path       = isset( $config['path'] )       ? $config['path']       : null;
		$cpcss_code = isset( $config['cpcss_code'] ) ? $config['cpcss_code'] : null;
		$file_path  = $this->config['vfs_dir']."1".DIRECTORY_SEPARATOR.$path;

		Functions\expect( 'get_current_blog_id' )->once()->andReturn( 1 );
		Functions\when( 'wp_strip_all_tags' )->returnArg();

		$data_manager = new DataManager( $this->config['vfs_dir'] );
		$actual = $data_manager->save_cpcss($path, $cpcss_code);

		$this->assertSame($expected['saved'], $actual);
		$this->assertTrue( $this->filesystem->exists( $file_path ) );

	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}

}
