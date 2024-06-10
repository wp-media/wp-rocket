<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Lazyload\Content;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\Lazyload\Content\Processor\Processor;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'lazyload_content_processor',
		'lazyload_content_subscriber',
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
		$this->getContainer()->add( 'lazyload_content_processor', Processor::class );
		$this->getContainer()->addShared( 'lazyload_content_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'lazyload_content_processor' ) );
	}
}
