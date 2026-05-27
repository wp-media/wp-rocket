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
	 * Options mock.
	 *
	 * @var Options_Data&\Mockery\MockInterface
	 */
	protected $options;

	/**
	 * Filesystem mock.
	 *
	 * @var Filesystem&\Mockery\MockInterface
	 */
	protected $filesystem;

	/**
	 * Context mock.
	 *
	 * @var ContextInterface&\Mockery\MockInterface
	 */
	protected $context;

	/**
	 * UsedCSS instance under test.
	 *
	 * @var UsedCSS
	 */
	protected $used_css;

	/**
	 * Set up the test environment.
	 *
	 * @return void
	 */
	protected function set_up() {
		parent::set_up();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->filesystem = Mockery::mock( Filesystem::class );
		$this->context    = Mockery::mock( ContextInterface::class );
		$query            = $this->createPartialMock( UsedCSS_Query::class, [] );
		$data_manager     = Mockery::mock( DataManager::class );
		$manager          = Mockery::mock( Manager::class );

		$this->used_css = new UsedCSS(
			$this->options,
			$query,
			$data_manager,
			$this->filesystem,
			$this->context,
			$manager
		);
	}

	/**
	 * Tests notice_write_permissions() shows or hides notices correctly.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected outcomes.
	 * @return void
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( $config['can_manage'] );

		if ( $config['can_manage'] ) {
			$this->options->shouldReceive( 'get' )
				->with( 'remove_unused_css', 0 )
				->andReturn( $config['is_enabled'] ? 1 : 0 );
		}

		if ( $config['can_manage'] && $config['is_enabled'] ) {
			$this->filesystem->shouldReceive( 'is_writable_folder' )
				->once()
				->andReturn( $config['is_writable_folder'] );
		}

		if ( $config['can_manage'] && $config['is_enabled'] && $config['is_writable_folder'] ) {
			Functions\expect( 'get_current_blog_id' )->once()->andReturn( 1 );

			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_rucss_subfolder_permission_error_1' )
				->andReturn( $config['transient_value'] );
		}

		if ( $expected['notice_called'] ) {
			Functions\expect( 'rocket_notice_writing_permissions' )
				->once()
				->andReturn( '<p>Write permissions error</p>' );

			Functions\expect( 'rocket_notice_html' )
				->once()
				->with(
					Mockery::on(
						function ( $args ) {
							return isset( $args['status'] ) && 'error' === $args['status'];
						}
					)
				);
		} else {
			Functions\expect( 'rocket_notice_writing_permissions' )->never();
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$this->used_css->notice_write_permissions();
	}
}
