<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS;

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
		'lazyload_css_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$root = apply_filters( 'rocket_lazyload_css_cache_root', 'background-css/' );
		$this->getLeagueContainer()->add( 'lazyload_css_cache', FilesystemCache::class )
			->addArgument( $root );
		$this->getContainer()->share( 'lazyload_css_subscriber', Subscriber::class );
	}
}
