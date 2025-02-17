<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CriticalPath;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\CriticalPath\Admin\{Admin, Post, Settings, Subscriber};

/**
 * Service provider for the Critical CSS classes
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
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
	 * Check if the service provider provides a specific service.
	 *
	 * @param string $id The id of the service.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {
		$filesystem        = rocket_direct_filesystem();
		$critical_css_path = new StringArgument( rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' ) );
		$template_path     = new StringArgument( $this->getContainer()->get( 'template_path' ) . '/cpcss' );

		$this->getContainer()->add( 'cpcss_api_client', APIClient::class );
		$this->getContainer()->add( 'cpcss_data_manager', DataManager::class )
			->addArguments(
				[
					$critical_css_path,
					$filesystem,
				]
			);
		$this->getContainer()->add( 'cpcss_service', ProcessorService::class )
			->addArguments(
				[
					'cpcss_data_manager',
					'cpcss_api_client',
				]
			);

		// REST CPCSS START.
		$this->getContainer()->add( 'rest_cpcss_wp_post', RESTWPPost::class )
			->addArguments(
				[
					'cpcss_service',
					'options',
				]
			);
		$this->getContainer()->addShared( 'rest_cpcss_subscriber', RESTCSSSubscriber::class )
			->addArgument( 'rest_cpcss_wp_post' );
		// REST CPCSS END.

		$this->getContainer()->add( 'critical_css_generation', CriticalCSSGeneration::class )
			->addArgument( 'cpcss_service' );
		$this->getContainer()->add( 'critical_css', CriticalCSS::class )
			->addArguments(
				[
					'critical_css_generation',
					'options',
					$filesystem,
				]
			);
		$this->getContainer()->addShared( 'critical_css_subscriber', CriticalCSSSubscriber::class )
			->addArguments(
				[
					'critical_css',
					'cpcss_service',
					'options',
					'options_api',
					'user',
					$filesystem,
				]
			);
		$this->getContainer()->add( 'cpcss_post', Post::class )
			->addArguments(
				[
					'options',
					'beacon',
					$critical_css_path,
					$template_path,
				]
			);
		$this->getContainer()->add( 'cpcss_settings', Settings::class )
			->addArguments(
				[
					'options',
					'beacon',
					'critical_css',
					$template_path,
				]
			);
		$this->getContainer()->add( 'cpcss_admin', Admin::class )
			->addArguments(
				[
					'options',
					'cpcss_service',
				]
			);
		$this->getContainer()->addShared( 'critical_css_admin_subscriber', Subscriber::class )
			->addArguments(
				[
					'cpcss_post',
					'cpcss_settings',
					'cpcss_admin',
				]
			);
	}
}
