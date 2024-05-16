<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\AboveTheFold\Admin\Controller;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Controller;

/**
 * Test class covering \WP_Rocket\Engine\Media\AboveTheFold\Admin\Controller::truncate_atf_admin
 *
 * @group AboveTheFold
 */
class Test_TruncateAtfAdmin extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Media/AboveTheFold/Admin/Controller/truncateAtfAdmin.php';

	protected $config;
	public function set_up() {
		parent::set_up();

		parent::installAtfTable();
	}

	public function tear_down() {
		parent::uninstallAtfTable();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->config = $config;
		$container    = apply_filters( 'rocket_container', null );
		$warm_up_controller = Mockery::mock( Controller::class );
		foreach ( $this->config['rows'] as $row ) {
			self::addLcp( $row );
		}
		Functions\expect( 'current_user_can' )->once()->with('rocket_manage_options')->andReturn($config['rocket_manage_options']);
		do_action( 'rocket_saas_clean_all' );

		$atf_query              = $container->get( 'atf_query' );
		$result_atf_after_clean = $atf_query->query();

		$this->assertCount( $expected, $result_atf_after_clean );
		if ( ! $expected ) {
			$this->assertSame( 1, did_action( 'rocket_after_clear_atf' ) );
		}

	}
}
