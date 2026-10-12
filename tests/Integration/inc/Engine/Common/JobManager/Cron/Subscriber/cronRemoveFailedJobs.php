<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Cron\Subscriber::cron_remove_failed_jobs
 *
 * @group  JobManager
 */
class Test_CronRemoveFailedJobs extends TestCase {
	use HttpRequestTrait;

	public function set_up() {
		parent::set_up();

		self::installUsedCssTable();
		self::installPreloadCacheTable();

		$this->setup_http();
	}
	public function tear_down() {
		self::uninstallUsedCssTable();
		self::uninstallPreloadCacheTable();

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		remove_filter( 'rocket_rocket_insights_enabled', '__return_false' );

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		add_filter( 'rocket_rocket_insights_enabled', '__return_false' );

		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

		foreach ( $input['used_css'] as $used_css ) {
			$rucss_usedcss_query->add_item( $used_css );
		}

		$result_used_css = $rucss_usedcss_query->query();
		$this->assertCount( count( $input['used_css'] ), $result_used_css );

		do_action( 'rocket_remove_saas_failed_jobs' );

		$rucss_usedcss_query     = $container->get( 'rucss_used_css_query' );
		$resultUsedCssAfterClean = $rucss_usedcss_query->query( [ 'status'  => 'to-submit' ] );

		$this->assertCount( count( $expected ), $resultUsedCssAfterClean );
	}
	public function set_rucss_option() {
		return 1;
	}
}
