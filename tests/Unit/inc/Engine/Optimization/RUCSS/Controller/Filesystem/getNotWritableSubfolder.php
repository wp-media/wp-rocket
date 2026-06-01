<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Controller\Filesystem;

use Brain\Monkey\Functions;
use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem::get_not_writable_subfolder
 *
 * @group RUCSS
 */
class Test_GetNotWritableSubfolder extends TestCase {
	/**
	 * @var Filesystem
	 */
	private $filesystem;

	protected function set_up() {
		parent::set_up();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\when( 'rocket_get_constant' )->returnArg();

		$wp_filesystem    = Mockery::mock( WP_Filesystem_Direct::class );
		$this->filesystem = new Filesystem( '/tmp/wpr-usedcss/', $wp_filesystem );
	}

	public function testShouldReturnEmptyStringWhenNoTransient() {
		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_rucss_subfolder_not_writable' )
			->andReturn( false );

		Functions\expect( 'delete_transient' )->never();

		$this->assertSame( '', $this->filesystem->get_not_writable_subfolder() );
	}

	public function testShouldReturnPathAndDeleteTransientWhenTransientExists() {
		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_rucss_subfolder_not_writable' )
			->andReturn( 'wp-content/wpr-usedcss/1/a/b' );

		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocket_rucss_subfolder_not_writable' );

		$this->assertSame( 'wp-content/wpr-usedcss/1/a/b', $this->filesystem->get_not_writable_subfolder() );
	}
}
