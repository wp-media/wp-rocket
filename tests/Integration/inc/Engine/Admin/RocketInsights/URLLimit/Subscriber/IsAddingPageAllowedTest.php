<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\URLLimit\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Engine\License\API\UserClient;

/**
 * @covers \WP_Rocket\Engine\Admin\RocketInsights\URLLimit\Subscriber::is_adding_page_allowed
 * @group RocketInsights
 * @group URLLimit
 */
class IsAddingPageAllowedTest extends TestCase {
    use DBTrait;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installPerformanceMonitoringTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPerformanceMonitoringTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		// Isolate the table hooks: setting the user reaches other tables' maybe_upgrade
		// callbacks on init/admin_init, which re-create their (temporary) tables and emit
		// "table already exists" DB errors. This removes those hooks (it does not install
		// any table); ri stays installed because the method under test queries it.
		self::removeDBHooks();
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


		$result = apply_filters('rocket_rocket_insights_allow_add_page', true);

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
