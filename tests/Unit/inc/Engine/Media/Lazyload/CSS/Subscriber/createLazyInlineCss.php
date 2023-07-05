<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Mockery;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\TagGenerator;
use WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\Extractor;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\RuleFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\FileResolver;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Filesystem_Direct;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\JsonFormatter;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::create_lazy_inline_css
 */
class Test_createLazyInlineCss extends TestCase {

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
     * @var JsonFormatter
     */
    protected $json_formatter;

	/**
	 * @var TagGenerator
	 */
	protected $tag_generator;

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
        $this->json_formatter = Mockery::mock(JsonFormatter::class);
		$this->tag_generator = Mockery::mock(TagGenerator::class);

        $this->subscriber = new Subscriber($this->extractor, $this->rule_formatter, $this->file_resolver, $this->filesystem_cache, $this->json_formatter, $this->tag_generator, $this->filesystem);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\when('wp_generate_uuid4')->justReturn('hash');

		foreach ($config['extract'] as $content => $urls) {
			$this->extractor->expects()->extract($content)->andReturn($urls);
		}

		foreach ($config['rule_format'] as $url_tag) {
			$this->rule_formatter->expects()->format($url_tag['content'], $url_tag['tag'])->andReturn($url_tag['new_content']);
			$this->json_formatter->expects()->format($url_tag['tag'])->andReturn($url_tag['formatted_urls']);
		}


		$this->assertSame($expected, $this->subscriber->create_lazy_inline_css($config['data']));
    }
}
