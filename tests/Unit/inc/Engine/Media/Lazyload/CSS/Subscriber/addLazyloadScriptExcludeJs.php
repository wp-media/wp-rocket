<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Engine\Media\Lazyload\CSS\Subscriber\SubscriberTrait;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::add_lazyload_script_exclude_js
 */
class Test_addLazyloadScriptExcludeJs extends TestCase {

	use SubscriberTrait;

	public function set_up() {
		$this->init_subscriber();
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->options->expects()->get('lazyload_css_bg_img', false)->andReturn($config['enabled']);
        $this->assertSame($expected, $this->subscriber->add_lazyload_script_exclude_js($config['js_files']));
    }
}
