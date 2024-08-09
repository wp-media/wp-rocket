<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\PerformanceHints\Admin\Controller;

use WP_Rocket\Engine\Common\PerformanceHints\Admin\Controller;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Test class covering \WP_Rocket\Engine\Common\PerformanceHints\Admin\Controller::truncate_from_admin
 *
 * @group PerformanceHints
 */
class Test_TruncateFromAdmin extends TestCase {
	protected $config;

	private $factories;
	private $queries;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install in set_up_before_class because of exists() requiring not temporary table.
		self::installAtfTable();
	}

	public static function tear_down_after_class() {
		self::installAtfTable();

		parent::tear_down_after_class();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->config = $config;
		$container    = apply_filters( 'rocket_container', null );

		foreach ( $this->config['rows'] as $row ) {
			self::addLcp( $row );
		}
		Functions\expect( 'current_user_can' )->once()->with('rocket_manage_options')->andReturn($config['rocket_manage_options']);
		do_action( 'rocket_performance_hints_clean_all', [] );

		$atf_query              = $container->get( 'atf_query' );
		$result_atf_after_clean = $atf_query->query();

		$this->assertCount( $expected, $result_atf_after_clean );
		if ( ! $expected ) {
			$this->assertSame( 1, did_action( 'rocket_after_clear_performance_hints_data' ) );
		}
	}
}
