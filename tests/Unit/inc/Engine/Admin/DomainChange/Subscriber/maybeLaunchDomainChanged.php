<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\DomainChange\Subscriber;

use WP_Rocket\Engine\Admin\DomainChange\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;

class Test_MaybeLaunchDomainChanged extends TestCase
{

	protected $subscriber;
	protected function set_up()
	{
		parent::set_up();
		$this->subscriber = new Subscriber();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		Functions\when('trailingslashit')->returnArg();
		Functions\expect('home_url')->andReturn($config['base_url']);
		Functions\expect('get_option')->with(Subscriber::LAST_BASE_URL_OPTION)->andReturn($config['last_base_url']);

		if($config['is_base_url_different'] || ! $config['base_url_exist']) {
			Functions\expect('update_option')->with(Subscriber::LAST_BASE_URL_OPTION, $expected);
		}

		if($config['is_base_url_different']) {
			Actions\expectDone('rocket_domain_changed');
		} else {
			Actions\expectDone('rocket_domain_changed')->never();
		}

		$this->subscriber->maybe_launch_domain_changed();
	}
}
