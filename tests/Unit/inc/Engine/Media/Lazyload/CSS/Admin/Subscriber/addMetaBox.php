<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Admin\Subscriber;

use WP_Rocket\Engine\Common\Cache\CacheInterface;
use WP_Rocket\Engine\Media\Lazyload\CSS\Admin\Subscriber;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Admin\Subscriber::add_meta_box
 */
class Test_addMetaBox extends TestCase {

    /**
     * @var Subscriber
     */
    protected $subscriber;

	/**
	 * @var CacheInterface
	 */
	protected $cache;

    public function set_up() {
        parent::set_up();

		$this->cache = \Mockery::mock(CacheInterface::class);

        $this->subscriber = new Subscriber($this->cache);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->stubTranslationFunctions();
        $this->assertSame($expected, $this->subscriber->add_meta_box($config['fields']));
    }
}
