<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\{Database,Settings,Subscriber};
use WP_Rocket\Engine\Optimization\RUCSS\Controller\{Queue,UsedCSS};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::spawn_cron
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
		Functions\expect('spawn_cron')->times($expected['spawnCronCalled']);

		if ($expected['spawnCronCalled']) {
			Functions\expect('check_ajax_referer')->times($expected['spawnCronCalled']);
			Functions\expect('current_user_can')->andReturn($config['current_user_can']);
			Functions\expect('wp_send_json_success')->times($expected['wp_send_json_success']);
			Functions\expect('wp_send_json_error')->times($expected['wp_send_json_error']);
		}

		$this->subscriber->spawn_cron();
	}
}
