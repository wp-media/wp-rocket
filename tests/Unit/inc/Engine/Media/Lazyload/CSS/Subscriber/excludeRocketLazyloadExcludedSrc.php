<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Engine\Media\Lazyload\CSS\Subscriber\SubscriberTrait;
use Mockery;
use WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\Extractor;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\RuleFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\FileResolver;
use WP_Rocket\Engine\Common\Cache\CacheInterface;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\MappingFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\TagGenerator;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Admin\Options_Data;
use WP_Filesystem_Direct;
use Brain\Monkey\Filters;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::exclude_rocket_lazyload_excluded_src
 */
class Test_excludeRocketLazyloadExcludedSrc extends TestCase {

	use SubscriberTrait;

    /**
     * @var Extractor
     */
    protected $extractor;

    /**
     * @var RuleFormatter
     */
    protected $rule_formatter;

    /**
     * @var FileResolver
     */
    protected $file_resolver;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var MappingFormatter
     */
    protected $mapping_formatter;

    /**
     * @var TagGenerator
     */
    protected $tag_generator;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var Options_Data
     */
    protected $options;

    /**
     * @var WP_Filesystem_Direct
     */
    protected $filesystem;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
		$this->init_subscriber();
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Filters\expectApplied('rocket_lazyload_excluded_src')->with([])->andReturn($config['excluded_src']);

        $this->assertSame($expected, $this->subscriber->exclude_rocket_lazyload_excluded_src($config['excluded'], $config['urls']));
    }
}
