<?php
namespace WP_Rocket\Engine\Capabilities;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service Provider for capabilities
 *
 * @since 3.6.3
 */
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
		'capabilities_manager',
		'capabilities_subscriber',
	];

	/**
	 * Returns common subscribers.
	 *
	 * @return string[]
	 */
	public function get_common_subscribers(): array {
		return [
			'rest_cpcss_subscriber',
			'critical_css_subscriber',
		];
	}

	/**
	 * Return IDs from admin subscribers.
	 *
	 * @return string[]
	 */
	public function get_admin_subscribers(): array {
		return [
			'critical_css_admin_subscriber',
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'capabilities_manager', Manager::class );
		$this->getContainer()->share( 'capabilities_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'capabilities_manager' ) )
			->addTag( 'common_subscriber' );
	}
}
