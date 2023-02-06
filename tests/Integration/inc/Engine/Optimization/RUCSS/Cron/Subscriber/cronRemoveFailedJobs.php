<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Cron\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber::cron_remove_failed_jobs
 *
 * @group  RUCSS
 */
class Test_CronRemoveFailedJobs extends TestCase {
	use DBTrait;

	public static function set_up_before_class() {
		self::installFresh();

		parent::set_up_before_class();
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::uninstallAll();
	}

	public function tear_down() : void {

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

        foreach ( $input['used_css'] as $used_css ) {
            $rucss_usedcss_query->add_item( $used_css );
        }

		$result_used_css = $rucss_usedcss_query->query();
		$this->assertCount( count( $input['used_css'] ), $result_used_css );

		do_action( 'rocket_remove_rucss_failed_jobs' );

		$rucss_usedcss_query     = $container->get( 'rucss_used_css_query' );
		$resultUsedCssAfterClean = $rucss_usedcss_query->query();

		$this->assertCount( count( $expected ), $resultUsedCssAfterClean );
	}
}
