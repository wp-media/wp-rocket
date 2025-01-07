<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Rocket\Engine\Media\Lazyload\CSS\Context\LazyloadCSSContext;
use WP_Rocket\Engine\Media\Lazyload\CSS\Data\LazyloadCSSContentFactory;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\{ContentFetcher,
	Extractor,
	FileResolver,
	MappingFormatter,
	RuleFormatter,
	TagGenerator};


/**
 * Service provider.
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'lazyload_css_cache',
		'lazyload_css_subscriber',
	];

	/**
	 * Check if the service provider provides a specific service.
	 *
	 * @param string $id The id of the service.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( 'lazyload_css_cache', FilesystemCache::class )
			->addArgument( apply_filters( 'rocket_lazyload_css_cache_root', 'background-css/' . get_current_blog_id() ) );

		$cache = $this->getContainer()->get( 'lazyload_css_cache' );

		$this->getContainer()->add( 'lazyload_css_context', LazyloadCSSContext::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $cache );

		$this->getContainer()->add( 'lazyload_css_fetcher', ContentFetcher::class );

		$this->getContainer()->add( 'lazyload_css_extractor', Extractor::class );
		$this->getContainer()->add( 'lazyload_css_file_resolver', FileResolver::class );
		$this->getContainer()->add( 'lazyload_css_json_formatter', MappingFormatter::class );
		$this->getContainer()->add( 'lazyload_css_rule_formatter', RuleFormatter::class );
		$this->getContainer()->add( 'lazyload_css_tag_generator', TagGenerator::class );

		$this->getContainer()->add( 'lazyload_css_factory', LazyloadCSSContentFactory::class );

		$this->getContainer()->addShared( 'lazyload_css_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'lazyload_css_extractor' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_css_rule_formatter' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_css_file_resolver' ) )
			->addArgument( $cache )
			->addArgument( $this->getContainer()->get( 'lazyload_css_json_formatter' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_css_tag_generator' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_css_fetcher' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_css_context' ) )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_css_factory' ) );
	}
}
