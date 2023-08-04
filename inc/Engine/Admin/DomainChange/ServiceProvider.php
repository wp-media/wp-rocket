<?php

namespace WP_Rocket\Engine\Admin\DomainChange;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;

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
		'domain_change_subscriber',
		'ajax_handler',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'ajax_handler', AjaxHandler::class );
		$this->getContainer()->add( 'domain_change_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'ajax_handler' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) );
	}
}
