<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Saas;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Saas\Admin\{AdminBar, Subscriber};

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
		'saas_admin_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'sass_admin_bar', Adminbar::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'atf_context' ) )
			->addArgument( $this->getContainer()->get( 'rucss_optimize_context' ) );
		$this->getContainer()->add( 'saas_admin_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'sass_admin_bar' ) );
	}
}
