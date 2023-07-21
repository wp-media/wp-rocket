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
use WP_Rocket\Admin\Options_Data;
use WP_Filesystem_Direct;
use Brain\Monkey\Filters;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::exclude_rocket_lazyload_excluded_src
 */
class Test_excludeRocketLazyloadExcludedSrc extends TestCase {

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
        $this->extractor = Mockery::mock(Extractor::class);
        $this->rule_formatter = Mockery::mock(RuleFormatter::class);
        $this->file_resolver = Mockery::mock(FileResolver::class);
        $this->cache = Mockery::mock(CacheInterface::class);
        $this->mapping_formatter = Mockery::mock(MappingFormatter::class);
        $this->tag_generator = Mockery::mock(TagGenerator::class);
        $this->context = Mockery::mock(ContextInterface::class);
        $this->options = Mockery::mock(Options_Data::class);
        $this->filesystem = Mockery::mock(WP_Filesystem_Direct::class);

        $this->subscriber = new Subscriber($this->extractor, $this->rule_formatter, $this->file_resolver, $this->cache, $this->mapping_formatter, $this->tag_generator, $this->context, $this->options, $this->filesystem);
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
