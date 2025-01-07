<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use WP_Filesystem_Direct;
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

trait SubscriberTrait {
	use HasLoggerTrait;

	protected $extractor;
	protected $rule_formatter;
	protected $file_resolver;
	protected $filesystem_cache;
	protected $json_formatter;
	protected $tag_generator;
	protected $fetcher;
	protected $context;
	protected $options;
	protected $lazyload_content_factory;
	protected $subscriber;
	protected $filesystem;

	protected function init_subscriber() {
		parent::set_up();
		$this->extractor                = Mockery::mock( Extractor::class );
		$this->rule_formatter           = Mockery::mock( RuleFormatter::class );
		$this->file_resolver            = Mockery::mock( FileResolver::class );
		$this->filesystem_cache         = Mockery::mock( FilesystemCache::class );
		$this->json_formatter           = Mockery::mock( MappingFormatter::class );
		$this->tag_generator            = Mockery::mock( TagGenerator::class );
		$this->fetcher                  = Mockery::mock( ContentFetcher::class );
		$this->context                  = Mockery::mock( ContextInterface::class );
		$this->options                  = Mockery::mock( Options_Data::class );
		$this->lazyload_content_factory = new LazyloadCSSContentFactory();
		$this->filesystem               = Mockery::mock( WP_Filesystem_Direct::class );

		$this->subscriber = new Subscriber( $this->extractor, $this->rule_formatter, $this->file_resolver, $this->filesystem_cache, $this->json_formatter, $this->tag_generator, $this->fetcher, $this->context, $this->options, $this->lazyload_content_factory, $this->filesystem );
		$this->set_logger( $this->subscriber );
	}
}
