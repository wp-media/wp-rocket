<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Controller\Filesystem;

use Brain\Monkey\Functions;
use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem::write_used_css
 * for the subfolder permission failure case.
 *
 * @group RUCSS
 */
class Test_WriteUsedCssSubfolderPermission extends TestCase {
	/**
	 * @var WP_Filesystem_Direct|Mockery\MockInterface
	 */
	private $wp_filesystem;

	/**
	 * @var Filesystem
	 */
	private $filesystem;

	protected function set_up() {
		parent::set_up();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'rocket_get_constant' )->returnArg();
		Functions\when( 'rocket_get_filesystem_perms' )->justReturn( 0644 );
		Functions\when( 'rocket_mkdir_p' )->justReturn( true );

		$this->wp_filesystem = Mockery::mock( WP_Filesystem_Direct::class );
		$this->filesystem    = new Filesystem( '/tmp/wpr-usedcss/', $this->wp_filesystem );
	}

	public function testShouldSetTransientWhenSubfolderNotWritable() {
		$this->wp_filesystem->shouldReceive( 'put_contents' )
			->once()
			->andReturn( false );

		$this->wp_filesystem->shouldReceive( 'is_writable' )
			->once()
			->andReturn( false );

		Functions\expect( 'set_transient' )
			->once()
			->with(
				'rocket_rucss_subfolder_not_writable',
				Mockery::type( 'string' ),
				HOUR_IN_SECONDS
			);

		$this->assertFalse( $this->filesystem->write_used_css( md5( 'test' ), 'body { color: red; }' ) );
	}

	public function testShouldNotSetTransientWhenWriteFailsForOtherReason() {
		$this->wp_filesystem->shouldReceive( 'put_contents' )
			->once()
			->andReturn( false );

		$this->wp_filesystem->shouldReceive( 'is_writable' )
			->once()
			->andReturn( true );

		Functions\expect( 'set_transient' )->never();

		$this->assertFalse( $this->filesystem->write_used_css( md5( 'test' ), 'body { color: red; }' ) );
	}
}
