<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Mockery;
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
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::add_lazyload_script_rocket_exclude_defer_js
 */
class Test_addLazyloadScriptRocketExcludeDeferJs extends TestCase {

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
     * @var WP_Filesystem_Direct
     */
    protected $filesystem;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
        $this->extractor = Mockery::mock(Extractor::class);
        $this->rule_formatter = Mockery::mock(RuleFormatter::class);
        $this->file_resolver = Mockery::mock(FileResolver::class);
        $this->cache = Mockery::mock(CacheInterface::class);
        $this->mapping_formatter = Mockery::mock(MappingFormatter::class);
        $this->tag_generator = Mockery::mock(TagGenerator::class);
        $this->context = Mockery::mock(ContextInterface::class);
        $this->filesystem = Mockery::mock(WP_Filesystem_Direct::class);

        $this->subscriber = new Subscriber($this->extractor, $this->rule_formatter, $this->file_resolver, $this->cache, $this->mapping_formatter, $this->tag_generator, $this->context, $this->filesystem);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, $this->subscriber->add_lazyload_script_rocket_exclude_defer_js($config['exclude_defer_js']));

    }
}
