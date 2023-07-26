<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Cron\Subscriber;

use ReflectionObject;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber::cron_clean_rows
 *
 * @group  RUCSS
 */
class Test_CronCleanRows extends TestCase {
	use DBTrait;

	protected function loadTestDataConfig() {
		$obj      = new ReflectionObject( $this );
		$filename = $obj->getFileName();

		$this->config = $this->getTestData( dirname( $filename ) . '/integration/', basename( $filename, '.php' ) );
	}

	public static function set_up_before_class() {
		self::installFresh();

		parent::set_up_before_class();
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::uninstallAll();
	}

	public function tear_down() : void {
		remove_filter( 'rocket_rucss_delete_interval', [ $this, 'set_rucss_delay' ] );

		parent::tear_down();
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

		add_filter( 'rocket_rucss_delete_interval', [ $this, 'set_rucss_delay' ] );
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


		if ( $this->input['delay'] ) {
			$this->assertCount( $count_remain_used_css,$resultUsedCssAfterClean );
		} else {
			$this->assertCount( count( $input['used_css'] ), $resultUsedCssAfterClean );
		}
	}

	public function set_rucss_delay() {
		return $this->input['delay'];
	}
}
