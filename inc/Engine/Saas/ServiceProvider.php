<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Saas;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Saas\Admin\{AdminBar, Clean, Notices, Subscriber};

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
		'saas_admin_bar',
		'saas_clean',
		'saas_notices',
		'saas_admin_subscriber',
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
		$this->getContainer()->add( 'saas_admin_bar', Adminbar::class )
			->addArguments(
				[
					'options',
					'rucss_optimize_context',
					new StringArgument( $this->getContainer()->get( 'template_path' ) . '/settings' ),
				]
			);
		$this->getContainer()->add( 'saas_clean', Clean::class );
		$this->getContainer()->add( 'saas_notices', Notices::class )
			->addArguments(
				[
					'options',
					'beacon',
				]
			);
		$this->getContainer()->addShared( 'saas_admin_subscriber', Subscriber::class )
			->addArguments(
				[
					'saas_admin_bar',
					'saas_clean',
					'saas_notices',
				]
			);
	}
}
