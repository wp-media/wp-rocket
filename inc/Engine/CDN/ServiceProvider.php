<?php
namespace WP_Rocket\Engine\CDN;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service provider for WP Rocket CDN
 *
 * @since 3.5.5
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('cdn_subscriber')
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->get_external( 'options' );

		$this->share( 'cdn', CDN::class )
			->addArgument( $options );
		$this->share( 'cdn_subscriber', Subscriber::class )
			->addArgument( $options )
			->addArgument( $this->get_internal( 'cdn' ) )
			->addTag( 'common_subscriber' );
	}
}
