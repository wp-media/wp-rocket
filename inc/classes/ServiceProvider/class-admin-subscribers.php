<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for admin subscribers
 *
 * @since 3.3
 * @author Remy Perona
 */
class Admin_Subscribers extends AbstractServiceProvider {

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
		'settings_page_subscriber',
		'deactivation_intent_render',
		'deactivation_intent_subscriber',
		'beacon_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'settings_page_subscriber', 'WP_Rocket\Subscriber\Admin\Settings\Page_Subscriber' )
			->withArgument( $this->getContainer()->get( 'settings_page' ) );
		$this->getContainer()->add( 'deactivation_intent_render', 'WP_Rocket\Admin\Deactivation\Render' )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/deactivation-intent' );
		$this->getContainer()->add( 'deactivation_intent_subscriber', 'WP_Rocket\Subscriber\Admin\Deactivation\Deactivation_Intent_Subscriber' )
			->withArgument( $this->getContainer()->get( 'deactivation_intent_render' ) )
			->withArgument( $this->getContainer()->get( 'options_api' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'beacon_subscriber', 'WP_Rocket\Subscriber\Admin\Settings\Beacon_Subscriber' )
			->withArgument( $this->getContainer()->get( 'beacon' ) );
	}
}
