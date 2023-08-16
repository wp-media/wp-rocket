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

	public function tear_down(){
		remove_filter('pre_http_request', [$this, 'edit_http_request']);
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		add_filter('pre_http_request', [$this, 'edit_http_request'], 10, 3);
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

        foreach ( $input['used_css'] as $used_css ) {
            $rucss_usedcss_query->add_item( $used_css );
        }

		$result_used_css = $rucss_usedcss_query->query();
		$this->assertCount( count( $input['used_css'] ), $result_used_css );

		do_action( 'rocket_remove_rucss_failed_jobs' );

		$rucss_usedcss_query     = $container->get( 'rucss_used_css_query' );
		$resultUsedCssAfterClean = $rucss_usedcss_query->query( [ 'status'  => 'pending' ] );

		$this->assertCount( count( $expected ), $resultUsedCssAfterClean );
	}
	public function edit_http_request($response, $args, $url) {
		return [
			'headers'=>[],
			'response' => array('code' => 200),
			'body'=>'{"code": 200,
			"message": "Added to Queue successfully.",
			"contents": {
				"jobId": "OVH_EU--496540278",
				"queueName": "EU",
				"isHome": false,
				"queueFullName": "rucssJob_EU"
				}
			}'
		];
	}
}
