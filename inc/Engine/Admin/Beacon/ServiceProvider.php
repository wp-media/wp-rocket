<?php
namespace WP_Rocket\Engine\Admin\Beacon;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service Provider for Beacon
 *
 * @since 3.3
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_admin_subscribers(): array
	{
		return [
			$this->getInternal('beacon')
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->share( 'beacon', Beacon::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'template_path' ) . '/settings' )
			->addArgument( $this->getContainer()->get( 'support_data' ) )
			->addTag( 'admin_subscriber' );
	}
}
