<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Abilities\Context;
use WP_Rocket\Engine\Abilities\Options\GetOptions;
use WP_Rocket\Engine\Abilities\Options\SetOption;
use WP_Rocket\Engine\Abilities\Options\Subscriber;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'abilities_context',
		'abilities_get_options',
		'abilities_set_option',
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
		$this->getContainer()->addShared( 'abilities_context', Context::class );
		$this->getContainer()->add( 'abilities_get_options', GetOptions::class )
			->addArgument( 'options' );
		$this->getContainer()->add( 'abilities_set_option', SetOption::class );
		$this->getContainer()->addShared( 'abilities_subscriber', Subscriber::class )
			->addArguments(
				[
					'abilities_get_options',
					'abilities_set_option',
					'abilities_context',
				]
			);
	}
}
