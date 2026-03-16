<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'abilities_options',
		'abilities_subscriber',
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
	 * Register the services provided by this service provider.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( 'abilities_options', Options::class )
			->addArgument( 'options' );
		$this->getContainer()->addShared( 'abilities_subscriber', Subscriber::class )
			->addArgument( 'abilities_options', );
	}
}
