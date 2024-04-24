<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\{Database,Settings,Subscriber};
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::spawn_cron
 *
 * @group RUCSS
 */
class Test_SpawnCron extends TestCase {

	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->subscriber = new Subscriber( Mockery::mock( Settings::class ), Mockery::mock( Database::class ), Mockery::mock( UsedCSS::class ), Mockery::mock( Queue::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected)
	{
		Functions\when('rocket_get_constant')->justReturn($config['rocket_get_constant']);

		Functions\when('current_user_can')->justReturn($config['current_user_can']);
		Functions\expect('wp_send_json_error')->times($expected['wp_send_json_error']);
		Functions\when('check_ajax_referer')->justReturn(true);
		Functions\when('wp_send_json_success')->justReturn(true);
		Functions\expect('spawn_cron')->times($expected['spawnCronCalled']);

		$this->subscriber->spawn_cron();
	}
}
