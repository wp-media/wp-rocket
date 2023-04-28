<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber;
use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Queue;
use Brain\Monkey\Functions;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::set_default_wp_rocket_no_licence
 */
class Test_setDefaultWpRocketNoLicence extends TestCase {

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var UsedCSS
     */
    protected $used_css;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
        $this->settings = Mockery::mock(Settings::class);
        $this->database = Mockery::mock(Database::class);
        $this->used_css = Mockery::mock(UsedCSS::class);
        $this->queue = Mockery::mock(Queue::class);

        $this->subscriber = new Subscriber($this->settings, $this->database, $this->used_css, $this->queue);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config )
    {
		Functions\expect('get_transient')->with('wp_rocket_no_licence')->once()->andReturn($config['initial_value']);
		if($config['initial_value'] === false) {
			Functions\expect('set_transient')->with('wp_rocket_no_licence', 0, WEEK_IN_SECONDS)->once();
		}
        $this->subscriber->set_default_wp_rocket_no_licence();
    }
}
