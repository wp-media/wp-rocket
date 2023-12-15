<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Saas;

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
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'sass_admin_bar', Adminbar::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'atf_context' ) )
			->addArgument( $this->getContainer()->get( 'rucss_optimize_context' ) )
			->addArgument( $this->getContainer()->get( 'template_path' ) );
		$this->getContainer()->add( 'sass_clean', Clean::class );
		$this->getContainer()->add( 'sass_notices', Notices::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addArgument( $this->getContainer()->get( 'atf_context' ) );
		$this->getContainer()->share( 'saas_admin_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'sass_admin_bar' ) )
			->addArgument( $this->getContainer()->get( 'sass_clean' ) )
			->addArgument( $this->getContainer()->get( 'sass_notices' ) );
	}
}
