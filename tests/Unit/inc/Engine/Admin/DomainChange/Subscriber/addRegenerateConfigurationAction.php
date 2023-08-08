<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\DomainChange\Subscriber;

use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\DomainChange\Subscriber;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\DomainChange\Subscriber::add_regenerate_configuration_action
 */
class Test_addRegenerateConfigurationAction extends TestCase {

    /**
     * @var AjaxHandler
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
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->stubTranslationFunctions();
		if(key_exists('action', $config['args']) && 'regenerate_configuration' == $config['args']['action']) {
			Functions\expect('admin_url')->with('admin-post.php')->andReturn($config['admin_url']);
			Functions\expect('wp_nonce_url')->with($config['admin_url'], 'rocket_regenerate_configuration')->andReturn($config['nonce']);
			Functions\expect('add_query_arg')->with([
				'action' => 'rocket_regenerate_configuration',
			], $config['nonce'])->andReturn($config['query_url']);
		}
        $this->assertSame($expected, $this->subscriber->add_regenerate_configuration_action($config['args']));
    }
}
