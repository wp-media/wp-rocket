<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Admin\Subscriber;

use Mockery;
use WP_Rocket\Engine\Media\Lazyload\CSS\Admin\Subscriber;
use WP_Rocket\Engine\Common\Cache\CacheInterface;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Admin\Subscriber::add_option_safemode
 */
class Test_addOptionSafemode extends TestCase {

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
        $this->cache = Mockery::mock(CacheInterface::class);

        $this->subscriber = new Subscriber($this->cache);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, $this->subscriber->add_option_safemode($config['options']));

    }
}
