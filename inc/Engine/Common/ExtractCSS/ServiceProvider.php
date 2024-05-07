<?php

namespace WP_Rocket\Engine\Common\ExtractCSS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;

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
		'common_extractcss_subscriber',
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
		/**
		 * Background CSS cache folder.
		 *
		 * @param string $root Background CSS cache folder.
		 */
		$root = apply_filters( 'rocket_lazyload_css_cache_root', 'background-css' );
		$this->getContainer()->add( 'lazyload_css_cache', FilesystemCache::class )
			->addArgument( $root );
		$this->getContainer()->addShared( 'common_extractcss_subscriber', Subscriber::class );
	}
}
