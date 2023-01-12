<?php

namespace WP_Rocket;

use Imagify_Partner;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Admin\Options;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\ThirdParty\Hostings\HostResolver;
use WP_Rocket\Addon\ServiceProvider as AddonServiceProvider;
use WP_Rocket\Addon\Varnish\ServiceProvider as VarnishServiceProvider;
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
	 * Get the subscribers to add to the event manager.
	 *
	 * @since 3.6
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
		];

		foreach ($initial_providers as $provider) {
			$this->container->addServiceProvider( $provider );
		}

		$providers = array_merge($initial_providers, [
			new SettingsServiceProvider(),
			new EngineAdminServiceProvider(),
			new OptimizationAdminServiceProvider(),
			new CapabilitiesServiceProvider(),
			new AddonServiceProvider(),
			new VarnishServiceProvider(),
			new PreloadServiceProvider(),
			new PreloadLinksServiceProvider(),
			new CDNServiceProvider(),
			new Common_Subscribers(),
			new ThirdPartyServiceProvider(),
			new HostingsServiceProvider(),
			new PluginServiceProvider(),
			new DelayJSServiceProvider(),
			new RUCSSServiceProvider(),
			new HeartbeatServiceProvider(),
			new DynamicListsServiceProvider(),
			new LicenseServiceProvider(),
			new ThemesServiceProvider(),
		]);

		if ( is_admin() ) {

			$this->init_admin_subscribers( $providers );
		} elseif ($this->is_valid_key ) {
			$this->init_valid_key_subscribers( $providers );
		}

		if ( ! is_admin() ) {
			$this->init_front_subscribers( $providers );
		}

		$this->init_common_subscribers( $providers );
	}


	/**
	 * Initializes the admin subscribers.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	private function init_admin_subscribers(array $providers) {
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

		$this->init_subscribers( $providers, 'get_admin_subscribers');
	}

	private function init_valid_key_subscribers(array $providers) {
		$subscribers = [];
		// Don't insert the LazyLoad file if Rocket LazyLoad is activated.
		if ( ! rocket_is_plugin_active( 'rocket-lazy-load/rocket-lazy-load.php' ) ) {
			$subscribers[] = 'lazyload_subscriber';
		}
		$this->init_subscribers( $providers, 'get_license_subscribers', $subscribers);
	}

	private function init_common_subscribers(array $providers) {
		$common_subscribers = [];
		$host_type = HostResolver::get_host_service();

		if ( ! empty( $host_type ) ) {
			$common_subscribers[] = $host_type;
		}

		if ( $this->options->get( 'do_cloudflare', false ) ) {
			$common_subscribers[] = 'cloudflare_subscriber';
		}

		$this->init_subscribers( $providers, 'get_common_subscribers', $common_subscribers);
	}


	private function init_front_subscribers(array $providers) {
		$this->init_subscribers( $providers, 'get_front_subscribers');
	}



	/**
	 * Initializes the admin subscribers.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	private function init_subscribers(array $providers, string $method, array $added = [] ) {
		$this->container->get('wp_rocket.engine.support.serviceprovider.support_data');
		/** @var AbstractServiceProvider[] $providers */
		foreach ($providers as $provider) {
			if(! $provider->$method() ) {
				continue;
			}
			$this->container->addServiceProvider( $provider );
			$this->add_subscribers( $provider->$method(), $added );
		}
	}

	protected function add_subscribers(array $subscribers, array $added = []) {

		foreach ( $subscribers as $subscriber ) {
			$this->event_manager->add_subscriber( $this->container->get( $subscriber ) );
		}
		foreach ( $added as $subscriber ) {
			$this->event_manager->add_subscriber( $this->container->get( $subscriber ) );
		}
	}
}
