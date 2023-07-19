<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Engine\Media\Lazyload\CSS\Subscriber\SubscriberTrait;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\Extractor;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\RuleFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\FileResolver;
use WP_Rocket\Engine\Common\Cache\CacheInterface;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\MappingFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\TagGenerator;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Filesystem_Direct;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::add_lazyload_script_rocket_delay_js_exclusions
 */
class Test_addLazyloadScriptRocketDelayJsExclusions extends TestCase {

	use SubscriberTrait;

	public function set_up() {
		$this->init_subscriber();
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, $this->subscriber->add_lazyload_script_rocket_delay_js_exclusions($config['js_files']));

    }
}
