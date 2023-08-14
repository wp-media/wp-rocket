<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Engine\Media\Lazyload\CSS\Subscriber\SubscriberTrait;
use Mockery;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\TagGenerator;
use WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\Extractor;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\RuleFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\FileResolver;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Filesystem_Direct;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\MappingFormatter;


use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::clear_generate_css_post
 */
class Test_clearGenerateCssPost extends TestCase {

	use SubscriberTrait;

	public function set_up() {
		$this->init_subscriber();
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		Functions\expect('get_permalink')->with($expected['post'])->andReturn($config['url']);
		if($config['should_delete']) {
			$this->filesystem_cache->expects()->delete($expected['url']);
		} else {
			$this->filesystem_cache->expects()->delete($expected['url'])->never();
		}
        $this->subscriber->clear_generate_css_post($config['post']);
	}
}
