<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\CriticalPath\Admin\Admin;
use WP_Rocket\Engine\CriticalPath\Admin\Post;
use WP_Rocket\Engine\CriticalPath\Admin\Settings;
use WP_Rocket\Engine\CriticalPath\Admin\Subscriber;

/**
 * Service provider for the Critical CSS classes
 *
 * @since 3.6
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
		'critical_css_generation',
		'critical_css',
		'critical_css_subscriber',
		'cpcss_api_client',
		'cpcss_data_manager',
		'cpcss_service',
		'rest_cpcss_wp_post',
		'rest_cpcss_subscriber',
		'cpcss_settings',
		'cpcss_post',
		'cpcss_admin',
		'critical_css_admin_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$filesystem        = rocket_direct_filesystem();
		$critical_css_path = rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' );
		$options           = $this->getContainer()->get( 'options' );
		$beacon            = $this->getContainer()->get( 'beacon' );
		$template_path     = $this->getContainer()->get( 'template_path' ) . '/cpcss';

		$this->getContainer()->share( 'cpcss_api_client', APIClient::class );
		$this->getContainer()->share( 'cpcss_data_manager', DataManager::class )
			->addArgument( $critical_css_path )
			->addArgument( $filesystem );
		$this->getContainer()->share( 'cpcss_service', ProcessorService::class )
			->addArgument( $this->getContainer()->get( 'cpcss_data_manager' ) )
			->addArgument( $this->getContainer()->get( 'cpcss_api_client' ) );

		$processor_service = $this->getContainer()->get( 'cpcss_service' );

		// REST CPCSS START.
		$this->getContainer()->share( 'rest_cpcss_wp_post', RESTWPPost::class )
			->addArgument( $processor_service )
			->addArgument( $options );
		$this->getContainer()->share( 'rest_cpcss_subscriber', RESTCSSSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rest_cpcss_wp_post' ) )
			->addTag( 'common_subscriber' );
		// REST CPCSS END.

		$this->getContainer()->add( 'critical_css_generation', CriticalCSSGeneration::class )
			->addArgument( $processor_service );
		$this->getContainer()->add( 'critical_css', CriticalCSS::class )
			->addArgument( $this->getContainer()->get( 'critical_css_generation' ) )
			->addArgument( $options )
			->addArgument( $filesystem );

		$critical_css = $this->getContainer()->get( 'critical_css' );

		$this->getContainer()->share( 'critical_css_subscriber', CriticalCSSSubscriber::class )
			->addArgument( $critical_css )
			->addArgument( $processor_service )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'user' ) )
			->addArgument( $filesystem )
			->addTag( 'common_subscriber' );

		$this->getContainer()->add( 'cpcss_post',  Post::class )
			->addArgument( $options )
			->addArgument( $beacon )
			->addArgument( $critical_css_path )
			->addArgument( $template_path );
		$this->getContainer()->add( 'cpcss_settings', Settings::class )
			->addArgument( $options )
			->addArgument( $beacon )
			->addArgument( $critical_css )
			->addArgument( $template_path );
		$this->getContainer()->add( 'cpcss_admin', Admin::class )
			->addArgument( $options )
			->addArgument( $processor_service );
		$this->getContainer()->share( 'critical_css_admin_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'cpcss_post' ) )
			->addArgument( $this->getContainer()->get( 'cpcss_settings' ) )
			->addArgument( $this->getContainer()->get( 'cpcss_admin' ) )
			->addTag( 'admin_subscriber' );
	}
}
