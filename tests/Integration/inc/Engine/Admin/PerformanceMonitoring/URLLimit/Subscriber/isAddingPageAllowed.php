<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\URLLimit\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\URLLimit\Subscriber;
use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring;
use WP_Rocket\Engine\License\API\UserClient;

/**
 * @covers \WP_Rocket\Engine\Admin\PerformanceMonitoring\URLLimit\Subscriber::is_adding_page_allowed
 *
 * @group PerformanceMonitoring
 * @group URLLimit
 * @group AdminOnly
 */
class Test_IsAddingPageAllowed extends TestCase {
    use DBTrait;


    /**
     * UserClient mock
     *
     * @var UserClient
     */
    private $user_client;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installPerformanceMonitoringTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPerformanceMonitoringTable();

		parent::tear_down_after_class();
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpectedValue($config, $expected) {
		$container = apply_filters('rocket_container', null);
		$user = $container->get('user');
		$user->set_user($config['customer_data']->generate());

        // Set up performance monitoring URLs count
        $this->setURLCount($config['urls_count']);

		$result = apply_filters('wpr_pm_allow_add_page', true);

        $this->assertEquals($expected, $result);
    }

    /**
     * Set the URL count in the performance monitoring table
     *
     * @param int $count Number of URLs to add
     */
    private function setURLCount($count) {

		if ($count > 0) {
			for ($i = 1; $i <= $count; $i++) {
				$this->addPerformanceMonitoring( [
					'url'             => "https://example.com/test-{$i}",
					'title'           => "Test Page {$i}",
					'is_mobile'       => 0,
					'job_id'          => "job-{$i}",
					'queue_name'      => 'rocket-performance-monitoring',
					'retries'         => 1,
					'status'          => 'completed',
					'data'            => '{}',
					'modified'        => current_time( 'mysql', true ),
					'last_accessed'   => current_time( 'mysql', true ),
					'next_retry_time' => current_time( 'mysql', true ),
				] );
			}
		}
    }
}
