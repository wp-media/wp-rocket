<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\DomainChange\Subscriber;

use Mockery;
use WP_Rocket\Engine\Admin\DomainChange\Subscriber;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;

class Test_MaybeLaunchDomainChanged extends TestCase
{

	protected $subscriber;

	protected $ajax_handler;

	protected function set_up()
	{
		parent::set_up();
		$this->ajax_handler = Mockery::mock(AjaxHandler::class);
		$this->subscriber = new Subscriber($this->ajax_handler);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		Functions\when('trailingslashit')->returnArg();
		Functions\when('rocket_get_constant')->returnArg(2);
		Functions\expect('home_url')->andReturn($config['base_url']);
		Functions\expect('get_option')->with(Subscriber::LAST_BASE_URL_OPTION)->andReturn($config['last_base_url']);

		if($config['is_base_url_different'] || ! $config['base_url_exist']) {
			Functions\expect('update_option')->with(Subscriber::LAST_BASE_URL_OPTION, $expected['encrypted_old_url']);
		}

		if($config['is_base_url_different']) {
			Actions\expectDone('rocket_detected_domain_changed')->with($expected['url'], $expected['old_url']);
			Functions\expect('set_transient')->with('rocket_domain_changed', $config['last_base_url'], 1209600);
		} else {
			Actions\expectDone('rocket_detected_domain_changed')->never();
		}

		$this->subscriber->maybe_launch_domain_changed();
	}
}
