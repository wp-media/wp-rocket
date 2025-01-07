<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Frontend\Subscriber;

use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber;
use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber::on_update
 */
class Test_onUpdate extends TestCase {

    /**
     * @var UsedCSS
     */
    protected $used_css;

	protected $context;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
        $this->used_css = Mockery::mock(UsedCSS::class);

		$this->context = Mockery::mock(ContextInterface::class);

        $this->subscriber = new Subscriber($this->used_css, $this->context);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		if($config['is_valid_version']) {
			Functions\expect('get_transient')->with('wp_rocket_no_licence')->atLeast()->once()->andReturn($config['has_transient']);
			Functions\expect('update_option')->with('wp_rocket_no_licence', $expected['has_transient']);
		} else {
			Functions\expect('get_transient')->with('wp_rocket_no_licence')->never();
		}

		$this->configureClearTransient($config, $expected);
        $this->subscriber->on_update($config['new_version'], $config['old_version']);
    }

	protected function configureClearTransient($config, $expected) {
		if(! $config['has_transient']) {
			return;
		}
		Functions\expect('delete_transient')->with('wp_rocket_no_licence');
	}
}
