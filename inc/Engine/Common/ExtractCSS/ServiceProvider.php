<?php

namespace WP_Rocket\Engine\Common\ExtractCSS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;

/**
 * Service provider.
 */
class ServiceProvider extends AbstractServiceProvider {


	/**
	 * The provided array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'lazyload_css_cache',
		'common_extractcss_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		/**
		 * Background CSS cache folder.
		 *
		 * @param string $root Background CSS cache folder.
		 */
		$root = apply_filters( 'rocket_lazyload_css_cache_root', 'background-css' );
		$this->getLeagueContainer()->add( 'lazyload_css_cache', FilesystemCache::class )
			->addArgument( $root );
		$this->getContainer()->share( 'common_extractcss_subscriber', Subscriber::class );
	}
}
