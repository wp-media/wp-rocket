<?php

namespace WP_Rocket\Engine\Preload\Activation;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Preload\Controller\Queue;
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
		'preload_caches_query',
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
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->add( 'preload_caches_table', CacheTable::class );
		$this->getContainer()->get( 'preload_caches_table' );

		$this->getContainer()->add( 'preload_caches_query', CacheQuery::class )
			->addArgument( new Logger() );
		$cache_query = $this->getContainer()->get( 'preload_caches_query' );

		$this->getContainer()->add( 'preload_queue', Queue::class );
		$queue = $this->getContainer()->get( 'preload_queue' );

		$this->getContainer()->add( 'preload_activation', Activation::class )
			->addArgument( $queue )
			->addArgument( $cache_query )
			->addArgument( $options );
	}
}
