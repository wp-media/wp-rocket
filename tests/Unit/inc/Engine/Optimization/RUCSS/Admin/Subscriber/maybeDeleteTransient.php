<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber;
use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Queue;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::maybe_delete_transient
 */
class Test_maybeDeleteTransient extends TestCase {

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
    public function testShouldDoAsExpected( $config, $expected )
    {
		if (! $expected) {
			Functions\expect('delete_transient')->with('wp_rocket_no_licence');
		}else {
			Functions\expect('delete_transient')->never();
		}
		$this->subscriber->maybe_delete_transient($config['old_value'], $config['value']);
	}
}
