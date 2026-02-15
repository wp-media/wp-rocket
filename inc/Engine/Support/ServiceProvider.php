<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket_Mobile_Detect;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'support_data',
		'support_rest',
		'support_meta',
		'support_subscriber',
		'mobile_detect',
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
	 * Registers the services in the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( 'mobile_detect', WP_Rocket_Mobile_Detect::class );

		$this->getContainer()->add( 'support_data', Data::class )
			->addArgument( 'options' );
		$this->getContainer()->add( 'support_rest', Rest::class )
			->addArguments(
				[
					'support_data',
					'options',
				]
			);
		$this->getContainer()->add( 'support_meta', Meta::class )
			->addArguments(
				[
					'mobile_detect',
					'options',
				]
			);
		$this->getContainer()->addShared( 'support_subscriber', Subscriber::class )
			->addArguments(
				[
					'support_rest',
					'support_meta',
				]
			);
	}
}
