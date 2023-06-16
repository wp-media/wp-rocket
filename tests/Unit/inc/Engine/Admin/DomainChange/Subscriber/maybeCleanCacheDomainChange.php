<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\DomainChange\Subscriber;

use Mockery;
use WP_Rocket\Engine\Admin\DomainChange\Subscriber;


use WP_Rocket\Engine\Common\Ajax\AjaxHandler;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
/**
 * @covers \WP_Rocket\Engine\Admin\DomainChange\Subscriber::maybe_clean_cache_domain_change
 */
class Test_maybeCleanCacheDomainChange extends TestCase {

    /**
     * @var Subscriber
     */
    protected $subscriber;

	protected $ajax_handler;

	public function set_up() {
        parent::set_up();
		$this->ajax_handler = Mockery::mock(AjaxHandler::class);

        $this->subscriber = new Subscriber($this->ajax_handler);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config )
    {
		Functions\when('rocket_get_constant')->returnArg();
		Functions\when('get_option')->alias(function ($name) use ($config) {
			if('WP_ROCKET_SLUG' === $name) {
				return $config['options'];
			}

			return false;
		});

		Filters\expectApplied('rocket_configurations_changed')->andReturn($config['rocket_configurations_changed']);

		if($config['options'] && ! $config['rocket_configurations_changed']) {
			Actions\expectDone('rocket_options_changed');
		}

        $this->subscriber->maybe_clean_cache_domain_change();
		$this->assertTrue(true);
    }
}
