<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\Minify\CSS\Subscriber;


use WP_Rocket\Engine\Optimization\Minify\ProcessorInterface;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Filesystem_Direct;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\Subscriber::on_update
 */
class Test_onUpdate extends TestCase {

    /**
     * @var Subscriber
     */
    protected $subscriber;

	/**
	 * Plugin options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Processor instance.
	 *
	 * @var ProcessorInterface
	 */
	protected $processor;

	/**
	 * Filesystem instance
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

    public function set_up() {
        parent::set_up();
		$this->options = Mockery::mock(Options_Data::class);
		$this->filesystem = Mockery::mock(WP_Filesystem_Direct::class);
        $this->subscriber = new Subscriber($this->options, $this->filesystem);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		if($expected) {
			Functions\expect('rocket_clean_minify');
			Functions\expect('rocket_clean_domain');
		}else {
			Functions\expect('rocket_clean_minify')->never();
			Functions\expect('rocket_clean_domain')->never();
		}
        $this->subscriber->on_update($config['old_version']);
    }
}
