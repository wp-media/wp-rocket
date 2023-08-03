<?php

namespace Engine\Media\Lazyload\CSS\Subscriber;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Media\Lazyload\CSS\Data\LazyloadCSSContentFactory;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\ContentFetcher;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\Extractor;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\FileResolver;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\MappingFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\RuleFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\TagGenerator;
use WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use Mockery;

trait SubscriberTrait
{
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
	 * @var MappingFormatter
	 */
	protected $json_formatter;

	/**
	 * @var TagGenerator
	 */
	protected $tag_generator;

	/**
	 * @var ContentFetcher
	 */
	protected $fetcher;

	/**
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * @var LazyloadCSSContentFactory
	 */
	protected $lazyload_content_factory;

	/**
	 * @var Subscriber
	 */
	protected $subscriber;

	protected function init_subscriber() {
		parent::set_up();
		$this->extractor = Mockery::mock(Extractor::class);
		$this->rule_formatter = Mockery::mock(RuleFormatter::class);
		$this->file_resolver = Mockery::mock(FileResolver::class);
		$this->filesystem_cache = Mockery::mock(FilesystemCache::class);
		$this->json_formatter = Mockery::mock(MappingFormatter::class);
		$this->tag_generator = Mockery::mock(TagGenerator::class);
		$this->fetcher = Mockery::mock(ContentFetcher::class);
		$this->context = Mockery::mock(ContextInterface::class);
		$this->options = Mockery::mock(Options_Data::class);
		$this->lazyload_content_factory = new LazyloadCSSContentFactory();

		$this->subscriber = new Subscriber($this->extractor, $this->rule_formatter, $this->file_resolver, $this->filesystem_cache, $this->json_formatter, $this->tag_generator, $this->fetcher, $this->context, $this->options, $this->lazyload_content_factory);
		$this->set_logger($this->subscriber);
	}
}
