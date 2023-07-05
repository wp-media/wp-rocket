<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\Extractor;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\FileResolver;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\MappingFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\RuleFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\TagGenerator;

class ServiceProvider extends AbstractServiceProvider {


	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'lazyload_css_subscriber',
		'rocket_css_image_lazyload_dir_level',
	];


	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {

		$this->getLeagueContainer()->add( 'lazyload_css_cache', FilesystemCache::class )
			->addArgument( apply_filters( 'rocket_css_image_lazyload_dir_level', 'background-css' ) );

		$this->getLeagueContainer()->add( 'lazyload_css_extractor', Extractor::class );
		$this->getLeagueContainer()->add( 'lazyload_css_file_resolver', FileResolver::class );
		$this->getLeagueContainer()->add( 'lazyload_css_json_formatter', MappingFormatter::class );
		$this->getLeagueContainer()->add( 'lazyload_css_rule_formatter', RuleFormatter::class );
		$this->getLeagueContainer()->add( 'lazyload_css_tag_generator', TagGenerator::class );
		$this->getLeagueContainer()->add( 'lazyload_css_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'lazyload_css_extractor' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_css_rule_formatter' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_css_file_resolver' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_css_cache' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_css_json_formatter' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_css_tag_generator' ) );
	}
}
