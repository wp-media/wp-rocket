<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload\Activation;

use WP_Filesystem_Direct;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Preload\Controller\{PreloadUrl, Queue};
use WP_Rocket\Engine\Preload\Database\Queries\Cache as CacheQuery;
use WP_Rocket\Engine\Preload\Database\Tables\Cache as CacheTable;
use WP_Rocket\Logger\Logger;

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
		'preload_cache_table',
		'preload_caches_query',
		'preload_url_controller',
		'preload_queue',
		'preload_activation',
	];

	/**
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getLeagueContainer()->get( 'options' );

		$this->getLeagueContainer()->add( 'preload_cache_table', CacheTable::class );
		$this->getLeagueContainer()->get( 'preload_cache_table' );
		$this->getLeagueContainer()->add( 'wp_direct_filesystem', WP_Filesystem_Direct::class )
			->addArgument( [] );
		$this->getLeagueContainer()->add( 'preload_cache_query', CacheQuery::class )
			->addArgument( new Logger() );
		$this->getLeagueContainer()->add( 'preload_queue', Queue::class );

		$cache_query = $this->getLeagueContainer()->get( 'preload_cache_query' );
		$queue       = $this->getLeagueContainer()->get( 'preload_queue' );

		$this->getLeagueContainer()->add( 'preload_url_controller', PreloadUrl::class )
			->addArgument( $options )
			->addArgument( $queue )
			->addArgument( $cache_query )
			->addArgument( $this->getLeagueContainer()->get( 'wp_direct_filesystem' ) );

		$this->getLeagueContainer()->add( 'preload_activation', Activation::class )
			->addArgument( $this->getLeagueContainer()->get( 'preload_url_controller' ) )
			->addArgument( $queue )
			->addArgument( $cache_query )
			->addArgument( $options );
	}
}
