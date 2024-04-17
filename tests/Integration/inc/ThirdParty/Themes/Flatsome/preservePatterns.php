<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Flatsome;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Themes\Flatsome;

class Test_PreservePatterns extends TestCase
{
    private $event;
    private $subscriber;
    public function set_up()
    {
        parent::set_up();
        $container = apply_filters('rocket_container', '');
        $this->event = $container->get('event_manager');
    }
    public function tear_down()
    {
        $this->event->remove_subscriber($this->subscriber);
        parent::tear_down();
    }
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected($config, $expected)
    {
        $this->subscriber = new Flatsome();
        $this->event->add_subscriber($this->subscriber);
        $this->assertSame($expected, apply_filters('rocket_rucss_inline_content_exclusions', $config['excluded']));
    }
}
