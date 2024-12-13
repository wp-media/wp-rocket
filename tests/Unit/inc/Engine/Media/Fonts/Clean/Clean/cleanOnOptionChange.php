<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Fonts\Clean\Clean;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Media\Fonts\Clean\Clean;
use WP_Rocket\Engine\Media\Fonts\Filesystem;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Fonts\Clean\Clean::clean_on_option_change
 * @group  HostFontsLocally
 */
class Test_CleanOnOptionChange extends TestCase {
	private $filesystem;
	private $clean;

	public function setUp(): void {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->filesystem = Mockery::mock( Filesystem::class );
		$this->clean      = new Clean( $this->filesystem );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $old_value, $value, $expected ) {
		if ( $expected ) {
			$this->filesystem->shouldReceive( 'delete_all_files_from_directory' )->once();
		} else {
			$this->filesystem->shouldNotReceive( 'delete_all_files_from_directory' );
		}

		$this->clean->clean_on_option_change( $old_value, $value );
	}
}
