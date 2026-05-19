<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Controller\UsedCSS;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Manager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::notice_write_permissions
 *
 * @group RUCSS
 */
class Test_NoticeWritePermissions extends TestCase {
	/**
	 * @var Options_Data|Mockery\MockInterface
	 */
	private $options;

	/**
	 * @var Filesystem|Mockery\MockInterface
	 */
	private $filesystem;

	/**
	 * @var UsedCSS
	 */
	private $used_css;

	protected function set_up() {
		parent::set_up();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->filesystem = Mockery::mock( Filesystem::class );
		$this->used_css   = new UsedCSS(
			$this->options,
			$this->createMock( UsedCSS_Query::class ),
			Mockery::mock( DataManager::class ),
			$this->filesystem,
			Mockery::mock( ContextInterface::class ),
			Mockery::mock( Manager::class )
		);

		Functions\when( 'rocket_get_constant' )->returnArg();
	}

	public function testShouldReturnEarlyWhenUserCannotManageOptions() {
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( false );

		$this->options->shouldNotReceive( 'get' );

		$this->used_css->notice_write_permissions();
	}

	public function testShouldReturnEarlyWhenRucssDisabled() {
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( true );

		$this->options->shouldReceive( 'get' )
			->with( 'remove_unused_css', 0 )
			->andReturn( 0 );

		$this->filesystem->shouldNotReceive( 'is_writable_folder' );

		$this->used_css->notice_write_permissions();
	}

	public function testShouldShowBasePathNoticeWhenBaseFolderNotWritable() {
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( true );

		$this->options->shouldReceive( 'get' )
			->with( 'remove_unused_css', 0 )
			->andReturn( 1 );

		$this->filesystem->shouldReceive( 'is_writable_folder' )
			->once()
			->andReturn( false );

		$this->filesystem->shouldNotReceive( 'get_not_writable_subfolder' );

		Functions\expect( 'rocket_notice_writing_permissions' )
			->once()
			->andReturn( '<p>error</p>' );

		Functions\expect( 'rocket_notice_html' )->once();

		$this->used_css->notice_write_permissions();
	}

	public function testShouldReturnEarlyWhenBaseFolderWritableAndNoSubfolderTransient() {
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( true );

		$this->options->shouldReceive( 'get' )
			->with( 'remove_unused_css', 0 )
			->andReturn( 1 );

		$this->filesystem->shouldReceive( 'is_writable_folder' )
			->once()
			->andReturn( true );

		$this->filesystem->shouldReceive( 'get_not_writable_subfolder' )
			->once()
			->andReturn( '' );

		Functions\expect( 'rocket_notice_html' )->never();

		$this->used_css->notice_write_permissions();
	}

	public function testShouldShowSubfolderNoticeWhenSubfolderNotWritable() {
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( true );

		$this->options->shouldReceive( 'get' )
			->with( 'remove_unused_css', 0 )
			->andReturn( 1 );

		$this->filesystem->shouldReceive( 'is_writable_folder' )
			->once()
			->andReturn( true );

		$this->filesystem->shouldReceive( 'get_not_writable_subfolder' )
			->once()
			->andReturn( 'wp-content/wpr-usedcss/1/a/b' );

		Functions\expect( 'rocket_notice_writing_permissions' )
			->once()
			->with( 'wp-content/wpr-usedcss/1/a/b' )
			->andReturn( '<p>subfolder error</p>' );

		Functions\expect( 'rocket_notice_html' )->once();

		$this->used_css->notice_write_permissions();
	}
}
