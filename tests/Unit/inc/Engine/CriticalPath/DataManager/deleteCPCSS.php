<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\DataManager;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;
use WP_Error;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\DataManager::delete_cpcss
 * @group CriticalPath
 * @group  vfs
 */
class Test_DeleteCPCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/DataManager/deleteCPCSS.php';
	protected static $mockCommonWpFunctionsInSetUp = true;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Error.php';
	}
	public function setUp()
	{
		parent::setUp();

		$this->whenRocketGetConstant();
	}

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$path         = isset( $config['path'] )         ? $config['path']         : null;
		$file_exists  = isset( $config['file_exists'] )  ? $config['file_exists']  : true;
		$file_deleted = isset( $config['file_deleted'] ) ? $config['file_deleted'] : true;

		Functions\expect( 'get_current_blog_id' )->once()->andReturn( 1 );
		Functions\when( 'wp_strip_all_tags' )->returnArg();

		$full_path = $this->config['vfs_dir'] . "1" . DIRECTORY_SEPARATOR . $path;

		if( $file_exists ){
			//Create the file and make it exists
			$this->filesystem->touch( $this->filesystem->getUrl( $full_path ) );
		}

		if( !$file_deleted ){
			//Here I want to make file deletion fail.
			//$this->filesystem->chmod( $this->config['vfs_dir'] . "1", 000 ); //tried to change file permissions.
			//$this->filesystem->chmod( $full_path, 000 ); //tried to change the containing folder permissions.
			//chown($this->filesystem->getUrl( $full_path ),465); //tried also change the ownership of the file.
		}

		$data_manager = new DataManager( $this->config['vfs_dir'] );
		$actual = $data_manager->delete_cpcss( $path );

		if( isset( $expected['deleted'] ) && true === $expected['deleted'] ){
			//Assert success.
			$this->assertSame( $expected['deleted'], $actual );
		}else{
			//Assert WP_Error.
			$this->assertInstanceOf(WP_Error::class, $actual);
			$this->assertSame( $expected['code'],    $actual->get_error_code() );
			$this->assertSame( $expected['message'], $actual->get_error_message() );
			$this->assertSame( $expected['data'],    $actual->get_error_data() );
		}

	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}

}
