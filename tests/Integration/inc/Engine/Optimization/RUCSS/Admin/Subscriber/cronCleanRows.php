<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::cron_clean_rows
 *
 * @group  RUCSS
 */
class Test_CronCleanRows extends FilesystemTestCase {
	use DBTrait;

	private $input;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/cronCleanRows.php';

	public static function setUpBeforeClass(): void {
		self::installFresh();

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::uninstallAll();
	}

	public function tearDown() : void {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $input ){
		$container              = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query   = $container->get( 'rucss_used_css_query' );
		$rucss_resources_query = $container->get( 'rucss_resources_query' );
		$current_date          = current_time( 'mysql', true );
		$old_date              = strtotime( $current_date. ' - 32 days' );

		$this->input = $input;
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		$this->set_permalink_structure( "/%postname%/" );

		foreach ( $input['deleted_used_css_files'] as $file => $content ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		$count_remain_used_css = 0;
		foreach ( $input['used_css'] as $used_css ) {
			if ( $old_date <  strtotime( $used_css['last_accessed']) ) {
				$count_remain_used_css ++;
			}
			$rucss_usedcss_query->add_item( $used_css );
		}

		$result_used_css = $rucss_usedcss_query->query();
		$this->assertCount( count( $input['used_css'] ), $result_used_css );


		$count_remain_resources = 0;
		foreach ( $input['resources'] as $resource ) {
			if ( $old_date <  strtotime( $resource['last_accessed']) ) {
				$count_remain_resources ++;
			}
			$rucss_resources_query->add_item( $resource );
		}

		$result_resources = $rucss_resources_query->query();
		$this->assertCount( count( $input['resources'] ), $result_resources );

		do_action( 'rocket_rucss_clean_rows_time_event' );

		$rucss_usedcss_query       = $container->get( 'rucss_used_css_query' );
		$rucss_resources_query     = $container->get( 'rucss_resources_query' );
		$resultUsedCssAfterClean   = $rucss_usedcss_query->query();
		$resultResourcesAfterClean = $rucss_resources_query->query();


		if ( $this->input['remove_unused_css'] ) {
			$this->assertCount( $count_remain_used_css,$resultUsedCssAfterClean );
			$this->assertCount( $count_remain_resources, $resultResourcesAfterClean );
		} else {
			$this->assertCount( count( $input['used_css'] ), $resultUsedCssAfterClean );
			$this->assertCount( count( $input['resources'] ), $resultResourcesAfterClean );
		}

		foreach ( $input['deleted_used_css_files'] as $file => $content ) {
			$this->assertFalse( $this->filesystem->exists( $file ) );
		}
	}

	public function set_rucss_option() {
		return $this->input['remove_unused_css'] ?? false;
	}
}
