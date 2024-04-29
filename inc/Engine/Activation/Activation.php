<?php

namespace WP_Rocket\Engine\Activation;

use WP_Rocket\Admin\Options;
use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\ServiceProvider\Options as OptionsServiceProvider;
use WP_Rocket\Engine\Preload\Activation\ServiceProvider as PreloadActivationServiceProvider;
use WP_Rocket\Engine\License\ServiceProvider as LicenseServiceProvider;
use WP_Rocket\Logger\ServiceProvider as LoggerServiceProvider;
use WP_Rocket\Engine\Media\AboveTheFold\Activation\ServiceProvider as AboveTheFoldActivationServiceProvider;
use WP_Rocket\ThirdParty\Hostings\HostResolver;
use WP_Rocket\ThirdParty\Hostings\ServiceProvider as HostingsServiceProvider;

/**
 * Plugin activation controller
 *
 * @since 3.6.3
 */
class Activation {
	/**
	 * Aliases in the container for each class that needs to call its activate method
	 *
	 * @var array
	 */
	private static $activators = [
		'advanced_cache',
		'capabilities_manager',
		'wp_cache',
		'action_scheduler_check',
		'preload_activation',
		'atf_activation',
	];

	/**
	 * Performs these actions during the plugin activation
	 *
	 * @return void
	 */
	public static function activate_plugin() {
		$container = new Container();

		$container->add( 'template_path', WP_ROCKET_PATH . 'views' );
		$options_api = new Options( 'wp_rocket_' );
		$container->add( 'options_api', $options_api );
		$container->addServiceProvider( new OptionsServiceProvider() );
		$container->addServiceProvider( new PreloadActivationServiceProvider() );
		$container->addServiceProvider( new ServiceProvider() );
		$container->addServiceProvider( new HostingsServiceProvider() );
		$container->addServiceProvider( new LicenseServiceProvider() );
		$container->addServiceProvider( new LoggerServiceProvider() );
		$container->get( 'logger' );
		$container->addServiceProvider( new AboveTheFoldActivationServiceProvider() );

		$host_type = HostResolver::get_host_service();

		if ( ! empty( $host_type ) ) {
			array_unshift( self::$activators, $host_type );
		}

		foreach ( self::$activators as $activator ) {
			$container->get( $activator );
		}

		// Last constants.
		define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
		define( 'WP_ROCKET_PLUGIN_SLUG', sanitize_key( WP_ROCKET_PLUGIN_NAME ) );

		if ( defined( 'SUNRISE' ) && SUNRISE === 'on' && function_exists( 'domain_mapping_siteurl' ) ) {
			require WP_ROCKET_INC_PATH . 'domain-mapping.php';
		}

		require WP_ROCKET_FUNCTIONS_PATH . 'options.php';
		require WP_ROCKET_FUNCTIONS_PATH . 'formatting.php';
		require WP_ROCKET_FUNCTIONS_PATH . 'i18n.php';
		require WP_ROCKET_FUNCTIONS_PATH . 'htaccess.php';
		require WP_ROCKET_FUNCTIONS_PATH . 'api.php';
		require WP_ROCKET_FUNCTIONS_PATH . 'admin.php';

		/**
		 * WP Rocket activation.
		 *
		 * @since  3.1.5
		 */
		do_action( 'rocket_activation' );

		if ( rocket_valid_key() ) {
			// Add All WP Rocket rules of the .htaccess file.
			flush_rocket_htaccess();
		}

		// Create the cache folders (wp-rocket & min).
		rocket_init_cache_dir();

		// Create the config folder (wp-rocket-config).
		rocket_init_config_dir();

		// Update customer key & licence.
		wp_remote_get(
			WP_ROCKET_WEB_API . 'activate-licence.php',
			[
				'blocking' => false,
			]
		);

		/**
		 * Fires after WP Rocket is activated
		 */
		do_action( 'rocket_after_activation' );
	}
}
