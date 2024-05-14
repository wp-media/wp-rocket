<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Cron\Subscriber::cron_clean_rows
 *
 * @group  JobManager
 */
class Test_CronCleanRows extends TestCase {
	private $input;

	public function tear_down() {
		remove_filter( 'rocket_saas_delete_interval', [ $this, 'set_rucss_delay' ] );
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input ) {
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );
		$current_date        = current_time( 'mysql', true );
		$old_date            = strtotime( $current_date . ' - 32 days' );

		$this->input = $input;

		add_filter( 'rocket_saas_delete_interval', [ $this, 'set_rucss_delay' ] );
		$this->set_permalink_structure( "/%postname%/" );

		$count_remain_used_css = 0;

		foreach ( $input['used_css'] as $used_css ) {
			if ( $old_date <  strtotime( $used_css['last_accessed'] ) ) {
				++$count_remain_used_css;
			}
			$rucss_usedcss_query->add_item( $used_css );
		}

		$result_used_css = $rucss_usedcss_query->query();
		$this->assertCount( count( $input['used_css'] ), $result_used_css );

		do_action( 'rocket_saas_clean_rows_time_event' );

		$rucss_usedcss_query     = $container->get( 'rucss_used_css_query' );
		$resultUsedCssAfterClean = $rucss_usedcss_query->query();


		if ( $this->input['delay'] ) {
			$this->assertCount( $count_remain_used_css, $resultUsedCssAfterClean );
		} else {
			$this->assertCount( count( $input['used_css'] ), $resultUsedCssAfterClean );
		}
	}

	public function set_rucss_delay() {
		return $this->input['delay'];
	}

	public function set_rucss_option() {
		return 1;
	}
}
