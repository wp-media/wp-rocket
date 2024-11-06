<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\Fonts\Admin\Settings;
use WP_Rocket\Engine\Media\Fonts\Admin\Subscriber as AdminSubscriber;

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
		'media_fonts_settings',
		'media_fonts_admin_subscriber',
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
	 * Registers the classes.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( 'media_fonts_settings', Settings::class );
		$this->getContainer()->addShared( 'media_fonts_admin_subscriber', AdminSubscriber::class )
			->addArgument( 'media_fonts_settings' );;
	}
}
