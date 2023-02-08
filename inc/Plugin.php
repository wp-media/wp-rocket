<?php

namespace WP_Rocket;

use Imagify_Partner;
use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Admin\API\ServiceProvider as APIServiceProvider;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\Hostings\HostResolver;
use WP_Rocket\Addon\ServiceProvider as AddonServiceProvider;
use WP_Rocket\Addon\Varnish\ServiceProvider as VarnishServiceProvider;
use WP_Rocket\Engine\Admin\ServiceProvider as AdminServiceProvider;
use WP_Rocket\Engine\Admin\Beacon\ServiceProvider as BeaconServiceProvider;
use WP_Rocket\Engine\Admin\Database\ServiceProvider as AdminDatabaseServiceProvider;
use WP_Rocket\Engine\Admin\ServiceProvider as EngineAdminServiceProvider;
use WP_Rocket\Engine\Admin\Settings\ServiceProvider as SettingsServiceProvider;
use WP_Rocket\Engine\Cache\ServiceProvider as CacheServiceProvider;
use WP_Rocket\Engine\Capabilities\ServiceProvider as CapabilitiesServiceProvider;
use WP_Rocket\Engine\CDN\RocketCDN\ServiceProvider as RocketCDNServiceProvider;
use WP_Rocket\Engine\CDN\ServiceProvider as CDNServiceProvider;
use WP_Rocket\Engine\CriticalPath\ServiceProvider as CriticalPathServiceProvider;
use WP_Rocket\Engine\HealthCheck\ServiceProvider as HealthCheckServiceProvider;
use WP_Rocket\Engine\Heartbeat\ServiceProvider as HeartbeatServiceProvider;
use WP_Rocket\Engine\License\ServiceProvider as LicenseServiceProvider;
use WP_Rocket\Engine\Media\ServiceProvider as MediaServiceProvider;
use WP_Rocket\Engine\Optimization\AdminServiceProvider as OptimizationAdminServiceProvider;
use WP_Rocket\Engine\Optimization\DeferJS\ServiceProvider as DeferJSServiceProvider;
use WP_Rocket\Engine\Optimization\DelayJS\ServiceProvider as DelayJSServiceProvider;
use WP_Rocket\Engine\Optimization\DynamicLists\ServiceProvider as DynamicListsServiceProvider;
use WP_Rocket\Engine\Optimization\RUCSS\ServiceProvider as RUCSSServiceProvider;
use WP_Rocket\Engine\Optimization\ServiceProvider as OptimizationServiceProvider;
use WP_Rocket\Engine\Plugin\ServiceProvider as PluginServiceProvider;
use WP_Rocket\Engine\Preload\Links\ServiceProvider as PreloadLinksServiceProvider;
use WP_Rocket\Engine\Preload\ServiceProvider as PreloadServiceProvider;
use WP_Rocket\Engine\Support\ServiceProvider as SupportServiceProvider;
use WP_Rocket\ServiceProvider\Common_Subscribers;
use WP_Rocket\ServiceProvider\Options as OptionsServiceProvider;
use WP_Rocket\ThirdParty\Hostings\ServiceProvider as HostingsServiceProvider;
use WP_Rocket\ThirdParty\ServiceProvider as ThirdPartyServiceProvider;
use WP_Rocket\ThirdParty\Themes\ServiceProvider as ThemesServiceProvider;

/**
 * Plugin Manager.
 */
class Plugin {

	/**
	 * Instance of Container class.
	 *
	 * @since 3.3
	 *
	 * @var Container instance
	 */
	private $container;

	/**
	 * Instance of the event manager.
	 *
	 * @since 3.6
	 *
	 * @var Event_Manager
	 */
	private $event_manager;

	/**
	 * Flag for if the license key is valid.
	 *
	 * @since 3.6
	 *
	 * @var bool
	 */
	private $is_valid_key;

	/**
	 * Instance of the Options.
	 *
	 * @since 3.6
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Instance of the Options_Data.
	 *
	 * @since 3.6
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Creates an instance of the Plugin.
	 *
	 * @since 3.0
	 *
	 * @param string    $template_path Path to the views.
	 * @param Container $container     Instance of the container.
	 */
	public function __construct( $template_path, Container $container ) {
		$this->container = $container;

		add_filter( 'rocket_container', [ $this, 'get_container' ] );

		$this->container->add( 'template_path', $template_path );
	}

	/**
	 * Returns the Rocket container instance.
	 *
	 * @return Container
	 */
	public function get_container() {
		return $this->container;
	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function load() {
		$this->event_manager = new Event_Manager();
		$this->container->share( 'event_manager', $this->event_manager );

		$this->options_api = new Options( 'wp_rocket_' );
		$this->container->add( 'options_api', $this->options_api );
		$this->container->addServiceProvider( OptionsServiceProvider::class );
		$this->options = $this->container->get( 'options' );

		$this->is_valid_key = rocket_valid_key();

		$this->load_subscribers();
	}

	/**
	 * Load necessary subscribers.
	 *
	 * @return void
	 */
	private function load_subscribers() {

		$initial_providers = [
			new AdminDatabaseServiceProvider(),
			new SupportServiceProvider(),
			new BeaconServiceProvider(),
			new RocketCDNServiceProvider(),
			new CacheServiceProvider(),
			new CriticalPathServiceProvider(),
			new HealthCheckServiceProvider(),
			new MediaServiceProvider(),
			new DeferJSServiceProvider(),
			new AddonServiceProvider(),
			new HostingsServiceProvider(),
		];

		$providers = [
			new SettingsServiceProvider(),
			new OptimizationServiceProvider(),
			new EngineAdminServiceProvider(),
			new OptimizationAdminServiceProvider(),
			new CapabilitiesServiceProvider(),
			new VarnishServiceProvider(),
			new PreloadServiceProvider(),
			new PreloadLinksServiceProvider(),
			new CDNServiceProvider(),
			new Common_Subscribers(),
			new ThirdPartyServiceProvider(),
			new PluginServiceProvider(),
			new DelayJSServiceProvider(),
			new LicenseServiceProvider(),
			new RUCSSServiceProvider(),
			new HeartbeatServiceProvider(),
			new DynamicListsServiceProvider(),
			new ThemesServiceProvider(),
			new AdminServiceProvider(),
		];

		$providers = $this->filter_right_providers( $providers );

		$providers = array_merge( $initial_providers, $providers );

		foreach ( $providers as $provider ) {
			$this->container->addServiceProvider( get_class( $provider ) );
		}

		if ( is_admin() ) {

			$this->init_admin_subscribers( $providers );
		} elseif ( $this->is_valid_key ) {
			$this->init_valid_key_subscribers( $providers );
		}

		if ( ! is_admin() ) {
			$this->init_front_subscribers( $providers );
		}

		$this->init_common_subscribers( $providers );
	}

	/**
	 * Filter service providers.
	 *
	 * @param AbstractServiceProvider[] $providers ServiceProviders.
	 *
	 * @return array
	 */
	public function filter_right_providers( array $providers ): array {

		$admin_providers   = [];
		$front_providers   = [];
		$license_providers = [];

		if ( is_admin() ) {
			$admin_providers = $this->filter_service_provider( $providers, 'get_admin_subscribers' );
		} elseif ( $this->is_valid_key ) {
			$subscribers = [];
			// Don't insert the LazyLoad file if Rocket LazyLoad is activated.
			if ( ! rocket_is_plugin_active( 'rocket-lazy-load/rocket-lazy-load.php' ) ) {
				$subscribers[] = 'lazyload_subscriber';
			}
			$license_providers = $this->filter_service_provider( $providers, 'get_license_subscribers', $subscribers );
		}

		if ( ! is_admin() ) {
			$front_providers = $this->filter_service_provider( $providers, 'get_front_subscribers' );
		}

		$common_subscribers = [];

		$host_type = HostResolver::get_host_service();

		if ( ! empty( $host_type ) ) {
			$common_subscribers[] = $host_type;
		}

		if ( $this->options->get( 'do_cloudflare', false ) ) {
			$common_subscribers[] = 'cloudflare_subscriber';
		}
		$common_providers = $this->filter_service_provider( $providers, 'get_common_subscribers', $common_subscribers );

		$providers = array_merge( $common_providers, $admin_providers, $license_providers, $front_providers );

		return array_unique( $providers, SORT_REGULAR );
	}

	/**
	 * Initializes the admin subscribers.
	 *
	 * @param AbstractServiceProvider[] $providers Services providers.
	 *
	 * @return void
	 * @since 3.6
	 */
	private function init_admin_subscribers( array $providers ) {
		if ( ! Imagify_Partner::has_imagify_api_key() ) {
			$imagify = new Imagify_Partner( 'wp-rocket' );
			$imagify->init();
			remove_action( 'imagify_assets_enqueued', 'imagify_dequeue_sweetalert_wprocket' );
		}

		$this->container->add(
			'settings_page_config',
			[
				'slug'       => WP_ROCKET_PLUGIN_SLUG,
				'title'      => WP_ROCKET_PLUGIN_NAME,
				'capability' => 'rocket_manage_options',
			]
		);

		$this->init_subscribers( $providers, 'get_admin_subscribers' );
	}

	/**
	 * Init subscribers that requires valid key.
	 *
	 * @param AbstractServiceProvider[] $providers Services providers.
	 *
	 * @return void
	 */
	private function init_valid_key_subscribers( array $providers ) {
		$subscribers = [];
		// Don't insert the LazyLoad file if Rocket LazyLoad is activated.
		if ( ! rocket_is_plugin_active( 'rocket-lazy-load/rocket-lazy-load.php' ) ) {
			$subscribers[] = 'lazyload_subscriber';
		}
		$this->init_subscribers( $providers, 'get_license_subscribers', $subscribers );
	}

	/**
	 * Init subscribers that are common.
	 *
	 * @param AbstractServiceProvider[] $providers Services providers.
	 *
	 * @return void
	 */
	private function init_common_subscribers( array $providers ) {
		$common_subscribers = [];

		$host_type = HostResolver::get_host_service();

		if ( ! empty( $host_type ) ) {
			$common_subscribers[] = $host_type;
		}

		if ( $this->options->get( 'do_cloudflare', false ) ) {
			$common_subscribers[] = 'cloudflare_subscriber';
		}

		$this->init_subscribers( $providers, 'get_common_subscribers', $common_subscribers );
	}


	/**
	 * Init front subscribers.
	 *
	 * @param AbstractServiceProvider[] $providers Services providers.
	 *
	 * @return void
	 */
	private function init_front_subscribers( array $providers ) {
		$this->init_subscribers( $providers, 'get_front_subscribers' );
	}

	/**
	 * Filters service providers.
	 *
	 * @param AbstractServiceProvider[] $providers Services providers.
	 * @param string                    $method name of the method to fetch subscribers ids.
	 * @param string[]                  $added id from subscribers manually added.
	 *
	 * @return AbstractServiceProvider[]
	 */
	public function filter_service_provider( array $providers, string $method, array $added = [] ) {
		return array_filter(
			$providers,
			function ( AbstractServiceProvider $provider ) use ( $method, $added ) {
				$subscribers = $provider->$method();

				if ( $subscribers && count( $subscribers ) > 0 ) {
					return $provider;
				}

				foreach ( $added as $item ) {
					if ( $provider->provides( $item ) ) {
						return $provider;
					}
				}

				return false;
			}
			);
	}

	/**
	 * Initializes the admin subscribers.
	 *
	 * @param AbstractServiceProvider[] $providers Services providers.
	 * @param string                    $method name of the method to fetch subscribers ids.
	 * @param string[]                  $added id from subscribers manually added.
	 *
	 * @return void
	 * @since 3.6
	 */
	private function init_subscribers( array $providers, string $method, array $added = [] ) {
		foreach ( $providers as $provider ) {
			if ( ! $provider->$method() ) {
				continue;
			}

			$this->add_subscribers( $provider->$method(), $added );
		}
	}

	/**
	 * Add subscribers to event manager.
	 *
	 * @param AbstractServiceProvider[] $subscribers Subscribers to add.
	 * @param string[]                  $added id from subscribers manually added.
	 *
	 * @return void
	 */
	protected function add_subscribers( array $subscribers, array $added = [] ) {
		foreach ( $subscribers as $subscriber ) {
			$this->event_manager->add_subscriber( $this->container->get( $subscriber ) );
		}
		foreach ( $added as $subscriber ) {
			$this->event_manager->add_subscriber( $this->container->get( $subscriber ) );
		}
	}
}
