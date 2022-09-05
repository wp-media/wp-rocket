<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Cron\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber::cron_clean_rows
 *
 * @group  RUCSS
 */
class Test_CronCleanRows extends TestCase {
	use DBTrait;

	private $input;

	public static function set_up_before_class() {
		self::installFresh();

		parent::set_up_before_class();
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::uninstallAll();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input ){
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );
		$current_date        = current_time( 'mysql', true );
		$old_date            = strtotime( $current_date. ' - 32 days' );

		$this->input = $input;
		$this->set_permalink_structure( "/%postname%/" );

		$count_remain_used_css = 0;
		foreach ( $input['used_css'] as $used_css ) {
			if ( $old_date <  strtotime( $used_css['last_accessed']) ) {
				$count_remain_used_css ++;
			}
			$rucss_usedcss_query->add_item( $used_css );
		}

		$result_used_css = $rucss_usedcss_query->query();
		$this->assertCount( count( $input['used_css'] ), $result_used_css );

		do_action( 'rocket_rucss_clean_rows_time_event' );

		$rucss_usedcss_query     = $container->get( 'rucss_used_css_query' );
		$resultUsedCssAfterClean = $rucss_usedcss_query->query();


		$this->assertCount( $count_remain_used_css, $resultUsedCssAfterClean );
	}
}
