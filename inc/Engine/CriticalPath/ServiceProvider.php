<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Rocket\AbstractServiceProvider;
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

	public function get_common_subscribers(): array
	{
		return [
			$this->getInternal('rest_cpcss_subscriber'),
			$this->getInternal('critical_css_subscriber'),
		];
	}

	public function get_admin_subscribers(): array
	{
		return [
			$this->getInternal('critical_css_admin_subscriber'),
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
		$options           = $this->getContainer()->get( 'options' );
		$beacon            = $this->getContainer()->get( 'beacon' );
		$template_path     = $this->getContainer()->get( 'template_path' ) . '/cpcss';

		$this->share( 'cpcss_api_client', APIClient::class );
		$this->share( 'cpcss_data_manager', DataManager::class )
			->addArgument( $critical_css_path )
			->addArgument( $filesystem );
		$this->share( 'cpcss_service', ProcessorService::class )
			->addArgument( $this->getInternal( 'cpcss_data_manager' ) )
			->addArgument( $this->getInternal( 'cpcss_api_client' ) );

		$processor_service = $this->getInternal( 'cpcss_service' );

		// REST CPCSS START.
		$this->share( 'rest_cpcss_wp_post', RESTWPPost::class )
			->addArgument( $processor_service )
			->addArgument( $options );
		$this->share( 'rest_cpcss_subscriber', RESTCSSSubscriber::class )
			->addArgument( $this->getInternal( 'rest_cpcss_wp_post' ) )
			->addTag( 'common_subscriber' );
		// REST CPCSS END.

		$this->add( 'critical_css_generation', CriticalCSSGeneration::class )
			->addArgument( $processor_service );
		$this->add( 'critical_css', CriticalCSS::class )
			->addArgument( $this->getInternal( 'critical_css_generation' ) )
			->addArgument( $options )
			->addArgument( $filesystem );

		$critical_css = $this->getInternal( 'critical_css' );

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
			->addArgument( $this->getInternal( 'cpcss_post' ) )
			->addArgument( $this->getInternal( 'cpcss_settings' ) )
			->addArgument( $this->getInternal( 'cpcss_admin' ) )
			->addTag( 'admin_subscriber' );
	}
}
