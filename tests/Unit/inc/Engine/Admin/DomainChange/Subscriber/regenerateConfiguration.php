<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\DomainChange\Subscriber;

use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\DomainChange\Subscriber;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\DomainChange\Subscriber::regenerate_configuration
 */
class Test_regenerateConfiguration extends TestCase {

    /**
     * @var Mockery\MockInterface|AjaxHandler
     */
    protected $ajax_handler;

	/**
	 * @var Beacon
	 */
	protected $beacon;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
        $this->ajax_handler = Mockery::mock(AjaxHandler::class);
		$this->beacon       = Mockery::mock(Beacon::class);

        $this->subscriber = new Subscriber($this->ajax_handler, $this->beacon);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
	    Functions\expect('get_option')->with('home')->andReturn($config['home_url']);
		Functions\when('trailingslashit')->returnArg();

		$this->ajax_handler->shouldReceive('validate_referer')->with('rocket_regenerate_configuration', 'rocket_manage_options')->andReturn($config['is_validated']);

		if( $config['is_validated']) {
			Functions\expect("get_transient")->with('rocket_domain_changed')->andReturn($config['transient']);
		}

		if($config['is_validated'] && $config['transient']) {
			Actions\expectDone('rocket_domain_changed')->with($config['home_url'], $config['last_base_url']);
			Functions\expect('delete_transient')->with('rocket_domain_changed');
			$this->ajax_handler->shouldReceive('redirect');
		} else {
			$this->expectNotToPerformAssertions();
		}

        $this->subscriber->regenerate_configuration();

    }
}
