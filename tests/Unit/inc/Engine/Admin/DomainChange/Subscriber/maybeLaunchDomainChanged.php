<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\DomainChange\Subscriber;

use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\DomainChange\Subscriber;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;

class Test_MaybeLaunchDomainChanged extends TestCase
{

	protected $subscriber;

	/**
	 * @var Beacon
	 */
	protected $beacon;


	protected $ajax_handler;

	protected function set_up()
	{
		parent::set_up();
		$this->ajax_handler = Mockery::mock(AjaxHandler::class);
		$this->beacon       = Mockery::mock(Beacon::class);

		$this->subscriber = new Subscriber($this->ajax_handler, $this->beacon);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		Functions\when('trailingslashit')->returnArg();
		Functions\when('rocket_get_constant')->returnArg(2);
		Functions\expect('get_option')->andReturnUsing(function( $option ) use ( $config ) {
			switch ( $option ) {
				case Subscriber::LAST_BASE_URL_OPTION:
					return $config['last_base_url'];

				case 'home':
					return $config['base_url'];
			}
		});

		if(!$config['ajax_request'] && ($config['is_base_url_different'] || ! $config['base_url_exist'])) {
			Functions\expect('update_option')->with(Subscriber::LAST_BASE_URL_OPTION, $expected['encrypted_old_url'], true);
		}

		Functions\expect('wp_doing_ajax')->once()->andReturn( $config['ajax_request'] );

		if(!$config['ajax_request'] && $config['is_base_url_different']) {
			Actions\expectDone('rocket_detected_domain_changed')->with($expected['url'], $expected['old_url']);
			Functions\expect('set_transient')->with('rocket_domain_changed', $config['last_base_url'], 1209600);
		} else {
			Actions\expectDone('rocket_detected_domain_changed')->never();
		}

		$this->subscriber->maybe_launch_domain_changed();
	}
}
