<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\Sucuri\Subscriber;

use Mockery;
use WP_Rocket\Addon\Sucuri\Subscriber;
use WP_Rocket\Admin\Options_Data;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Addon\Sucuri\Subscriber::add_cdn_helper_message
 */
class Test_addCdnHelperMessage extends TestCase {

    /**
     * @var Options_Data
     */
    protected $options;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
        $this->options = Mockery::mock(Options_Data::class);

        $this->subscriber = new Subscriber($this->options);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->options->expects()->get('sucury_waf_cache_sync', false)->andReturn($config['is_enabled']);
        $this->assertSame($expected, $this->subscriber->add_cdn_helper_message($config['addons']));
    }
}
