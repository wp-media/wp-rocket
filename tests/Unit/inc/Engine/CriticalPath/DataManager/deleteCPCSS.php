<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\DataManager;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;
use WP_Error;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\DataManager::delete_cpcss
 *
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
		$file_deleted = isset( $config['file_deleted'] ) ? $config['file_deleted'] : true;

		Functions\expect( 'get_current_blog_id' )->once()->andReturn( 1 );
		Functions\when( 'wp_strip_all_tags' )->returnArg();

		$full_path = $this->config['vfs_dir'] . "1" . DIRECTORY_SEPARATOR . $path;

		if( !$file_deleted ){
			//Here I want to simulate file deletion fail.
			$this->filesystem->chmod( $this->filesystem->getUrl( $this->config['vfs_dir'] . "posts/1" ), 0000 );
			$this->filesystem->chmod( $full_path, 0000 );
		}

		$data_manager = new DataManager( $this->config['vfs_dir'] );
		$actual = $data_manager->delete_cpcss( $path );

		if( isset( $expected['deleted'] ) && true === $expected['deleted'] ){
			//Assert success.
			$this->assertSame( $expected['deleted'], $actual );
			$this->assertFalse( $this->filesystem->exists( $full_path ) );
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
