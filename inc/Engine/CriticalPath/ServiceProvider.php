<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\CriticalPath\Admin\Admin;
use WP_Rocket\Engine\CriticalPath\Admin\Post;
use WP_Rocket\Engine\CriticalPath\Admin\Settings;
use WP_Rocket\Engine\CriticalPath\Admin\Subscriber;
use WP_Rocket\Engine\Admin\Beacon\ServiceProvider as BeaconServiceProvider;

/**
 * Service provider for the Critical CSS classes
 *
 * @since 3.6
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('rest_cpcss_subscriber'),
			$this->generate_container_id('critical_css_subscriber'),
		];
	}

	public function get_admin_subscribers(): array
	{
		return [
			$this->generate_container_id('critical_css_admin_subscriber'),
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$filesystem        = rocket_direct_filesystem();
		$critical_css_path = rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' );
		$options           = $this->get_external( 'options' );
		$beacon            = $this->get_external( 'beacon', BeaconServiceProvider::class );
		$template_path     = $this->get_external( 'template_path' ) . '/cpcss';

		$this->share( 'cpcss_api_client', APIClient::class );
		$this->share( 'cpcss_data_manager', DataManager::class )
			->addArgument( $critical_css_path )
			->addArgument( $filesystem );
		$this->share( 'cpcss_service', ProcessorService::class )
			->addArgument( $this->get_internal( 'cpcss_data_manager' ) )
			->addArgument( $this->get_internal( 'cpcss_api_client' ) );

		$processor_service = $this->get_internal( 'cpcss_service' );

		// REST CPCSS START.
		$this->share( 'rest_cpcss_wp_post', RESTWPPost::class )
			->addArgument( $processor_service )
			->addArgument( $options );
		$this->share( 'rest_cpcss_subscriber', RESTCSSSubscriber::class )
			->addArgument( $this->get_internal( 'rest_cpcss_wp_post' ) )
			->addTag( 'common_subscriber' );
		// REST CPCSS END.

		$this->add( 'critical_css_generation', CriticalCSSGeneration::class )
			->addArgument( $processor_service );
		$this->add( 'critical_css', CriticalCSS::class )
			->addArgument( $this->get_internal( 'critical_css_generation' ) )
			->addArgument( $options )
			->addArgument( $filesystem );

		$critical_css = $this->get_internal( 'critical_css' );

		$this->share( 'critical_css_subscriber', CriticalCSSSubscriber::class )
			->addArgument( $critical_css )
			->addArgument( $processor_service )
			->addArgument( $options )
			->addArgument( $filesystem )
			->addTag( 'common_subscriber' );

		$this->add( 'cpcss_post',  Post::class )
			->addArgument( $options )
			->addArgument( $beacon )
			->addArgument( $critical_css_path )
			->addArgument( $template_path );
		$this->add( 'cpcss_settings', Settings::class )
			->addArgument( $options )
			->addArgument( $beacon )
			->addArgument( $critical_css )
			->addArgument( $template_path );
		$this->add( 'cpcss_admin', Admin::class )
			->addArgument( $options )
			->addArgument( $processor_service );
		$this->share( 'critical_css_admin_subscriber', Subscriber::class )
			->addArgument( $this->get_internal( 'cpcss_post' ) )
			->addArgument( $this->get_internal( 'cpcss_settings' ) )
			->addArgument( $this->get_internal( 'cpcss_admin' ) )
			->addTag( 'admin_subscriber' );
	}
}
