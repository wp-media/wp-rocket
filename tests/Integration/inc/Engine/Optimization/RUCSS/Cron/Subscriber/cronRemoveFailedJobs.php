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

	private $add_to_queue_response;
	public static function set_up_before_class() {
		self::installFresh();

		parent::set_up_before_class();
	}
	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::uninstallAll();
	}
	public function set_up() {
		parent::set_up();
		add_filter('pre_http_request', [$this, 'edit_http_request'], 10, 3);
	}
	public function tear_down(){
		remove_filter('pre_http_request', [$this, 'edit_http_request']);
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		$this->add_to_queue_response = $input['add_job_to_queue_response'];
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

        foreach ( $input['used_css'] as $used_css ) {
            $rucss_usedcss_query->add_item( $used_css );
        }

		$result_used_css = $rucss_usedcss_query->query();
		$this->assertCount( count( $input['used_css'] ), $result_used_css );

		do_action( 'rocket_remove_rucss_failed_jobs' );

		$rucss_usedcss_query     = $container->get( 'rucss_used_css_query' );
		$resultUsedCssAfterClean = $rucss_usedcss_query->query( [ 'status'  => 'to-submit' ] );
		$this->assertCount( count( $expected ), $resultUsedCssAfterClean );
	}
	public function edit_http_request($response, $args, $url) {
		return $this->add_to_queue_response;
	}
}
