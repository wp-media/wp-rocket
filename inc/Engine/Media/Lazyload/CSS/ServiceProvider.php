<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Lazyload\CSS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
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
		'lazyload_css_context',
		'lazyload_css_content_fetcher',
		'lazyload_css_extractor',
		'lazyload_css_file_resolver',
		'lazyload_css_mapping_formatter',
		'lazyload_css_rule_formatter',
		'lazyload_css_tag_generator',
		'lazyload_css_content_factory',
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
		$this->getContainer()->add( 'lazyload_css_context', LazyloadCSSContext::class )
			->addArguments(
				[
					'options',
					'lazyload_css_cache',
				]
			);
		$this->getContainer()->add( 'lazyload_css_content_fetcher', ContentFetcher::class );
		$this->getContainer()->add( 'lazyload_css_extractor', Extractor::class );
		$this->getContainer()->add( 'lazyload_css_file_resolver', FileResolver::class );
		$this->getContainer()->add( 'lazyload_css_mapping_formatter', MappingFormatter::class );
		$this->getContainer()->add( 'lazyload_css_rule_formatter', RuleFormatter::class );
		$this->getContainer()->add( 'lazyload_css_tag_generator', TagGenerator::class );
		$this->getContainer()->add( 'lazyload_css_content_factory', LazyloadCSSContentFactory::class );
		$this->getContainer()->addShared( 'lazyload_css_subscriber', Subscriber::class )
			->addArguments(
				[
					'lazyload_css_extractor',
					'lazyload_css_rule_formatter',
					'lazyload_css_file_resolver',
					'lazyload_css_cache',
					'lazyload_css_mapping_formatter',
					'lazyload_css_tag_generator',
					'lazyload_css_content_fetcher',
					'lazyload_css_context',
					'options',
					'lazyload_css_content_factory',
				]
			);
	}
}
