<?php
namespace WP_Rocket\Engine\Preload\Links;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service provider for WP Rocket preload links.
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->getInternal('preload_links_admin_subscriber'),
			$this->getInternal('preload_links_subscriber'),
		];
	}

	/**
	 * Registers the subscribers in the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->share( 'preload_links_admin_subscriber', AdminSubscriber::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->share( 'preload_links_subscriber', Subscriber::class )
			->addArgument( $options )
			->addArgument( rocket_direct_filesystem() )
			->addTag( 'common_subscriber' );
	}
}
