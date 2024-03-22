<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Common\JobManager\Cron\Subscriber::schedule_on_submit_jobs
 * 
 * @group JobManager
 */
class Test_scheduleOnSubmitJobs extends TestCase {

	protected $config;

	public function set_up()
	{
		parent::set_up();
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'rucss' ] );
	}

	public function tear_down()
	{
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'rucss' ] );
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		$this->config = $config;

		if ( $config['scheduled'] ) {
			wp_schedule_event( time(), 'rocket_saas_on_submit_jobs', 'rocket_saas_on_submit_jobs' );
		}

		do_action('init');


		if ( $expected ) {
			$this->assertNotFalse( wp_next_scheduled( 'rocket_saas_on_submit_jobs' ) );
		} else {
			$this->assertFalse( wp_next_scheduled( 'rocket_saas_on_submit_jobs' ) );
		}
    }

	public function rucss() {
		return $this->config['rucss'];
	}
}
