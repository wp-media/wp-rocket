<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

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

	use HasLoggerTrait;

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
     * @var FilesystemCache
     */
    protected $filesystem_cache;

    /**
     * @var WP_Filesystem_Direct
     */
    protected $filesystem;

    /**
     * @var MappingFormatter
     */
    protected $json_formatter;

	/**
	 * @var TagGenerator
	 */
	protected $tag_generator;

	/**
	 * @var ContextInterface
	 */
	protected $context;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function set_up() {
        parent::set_up();
        $this->extractor = Mockery::mock(Extractor::class);
        $this->rule_formatter = Mockery::mock(RuleFormatter::class);
        $this->file_resolver = Mockery::mock(FileResolver::class);
        $this->filesystem_cache = Mockery::mock(FilesystemCache::class);
        $this->filesystem = Mockery::mock(WP_Filesystem_Direct::class);
        $this->json_formatter = Mockery::mock(MappingFormatter::class);
		$this->tag_generator = Mockery::mock(TagGenerator::class);
		$this->context = Mockery::mock(ContextInterface::class);

        $this->subscriber = new Subscriber($this->extractor, $this->rule_formatter, $this->file_resolver, $this->filesystem_cache, $this->json_formatter, $this->tag_generator, $this->context, $this->filesystem);
    	$this->set_logger($this->subscriber);
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		Functions\expect('get_post_permalink')->with($expected['post'])->andReturn($config['url']);
		if($config['should_delete']) {
			$this->filesystem_cache->expects()->delete($expected['url']);
		} else {
			$this->filesystem_cache->expects()->delete($expected['url'])->never();
		}
        $this->subscriber->clear_generate_css_post($config['post']);
	}
}
