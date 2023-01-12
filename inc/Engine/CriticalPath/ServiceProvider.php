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

	public function declare()
	{
		$filesystem        = rocket_direct_filesystem();
		$critical_css_path = rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' );

		$this->register_service('cpcss_api_client', function ($id) {
			$this->share( $id, APIClient::class );
		});

		$this->register_service('cpcss_data_manager', function ($id) use ($filesystem, $critical_css_path) {
			$this->share( $id, DataManager::class )
				->addArgument( $critical_css_path )
				->addArgument( $filesystem );
		});

		$this->register_service('cpcss_service', function ($id) {
			$this->share( $id, ProcessorService::class )
				->addArgument( $this->get_internal( 'cpcss_data_manager' ) )
				->addArgument( $this->get_internal( 'cpcss_api_client' ) );
		});

		// REST CPCSS START.
		$this->register_service('rest_cpcss_wp_post', function ($id) {
			$this->share( $id, RESTWPPost::class )
				->addArgument( $this->get_internal( 'cpcss_service' ) )
				->addArgument( $this->get_external('options') );
		});

		$this->register_service('rest_cpcss_subscriber', function ($id) {
			$this->share( $id, RESTCSSSubscriber::class )
				->addArgument( $this->get_internal( 'rest_cpcss_wp_post' ) )
				->addTag( 'common_subscriber' );
		});

		// REST CPCSS END.
		$this->register_service('critical_css_generation', function ($id) {
			$this->add( $id, CriticalCSSGeneration::class )
				->addArgument( $this->get_internal( 'cpcss_service' ) );
		});

		$this->register_service('critical_css', function ($id) use ($filesystem) {

			$this->add( $id, CriticalCSS::class )
				->addArgument( $this->get_internal( 'critical_css_generation' ) )
				->addArgument( $this->get_external('options') )
				->addArgument( $filesystem );
		});

		$this->register_service('critical_css_subscriber', function ($id) use ($filesystem) {
			$this->share( $id, CriticalCSSSubscriber::class )
				->addArgument( $this->get_internal( 'critical_css' ) )
				->addArgument( $this->get_internal( 'cpcss_service' ) )
				->addArgument( $this->get_external('options') )
				->addArgument( $filesystem )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('cpcss_post', function ($id) use ($critical_css_path) {
			$this->add( $id,  Post::class )
				->addArgument( $this->get_external('options') )
				->addArgument( $this->get_external( 'beacon', BeaconServiceProvider::class ) )
				->addArgument( $critical_css_path )
				->addArgument( $this->get_external( 'template_path' ) . '/cpcss' );
		});

		$this->register_service('cpcss_settings', function ($id) {
			$this->add( $id, Settings::class )
				->addArgument( $this->get_external('options') )
				->addArgument( $this->get_external( 'beacon', BeaconServiceProvider::class ) )
				->addArgument( $this->get_internal( 'critical_css' ) )
				->addArgument( $this->get_external( 'template_path' ) . '/cpcss' );
		});

		$this->register_service('cpcss_admin', function ($id) {
			$this->add( $id, Admin::class )
				->addArgument( $this->get_external('options') )
				->addArgument( $this->get_internal( 'cpcss_service' ) );
		});

		$this->register_service('critical_css_admin_subscriber', function ($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_internal( 'cpcss_post' ) )
				->addArgument( $this->get_internal( 'cpcss_settings' ) )
				->addArgument( $this->get_internal( 'cpcss_admin' ) )
				->addTag( 'admin_subscriber' );
		});
	}
}
