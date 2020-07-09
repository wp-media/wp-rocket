<?php

namespace WP_Rocket\Engine\Activation;

use League\Container\Container;
use WP_Rocket\Engine\Cache\AdvancedCache;

class Activation {
	/**
	 * Aliases in the container for each class that needs to call its activate method
	 *
	 * @var array
	 */
	private static $activators = [
		'capabilities_manager',
		'wp_cache',
	];

	/**
	 * Performs these actions during the plugin activation
	 *
	 * @return void
	 */
	public static function activate() {
		$container  = new Container();
		$filesystem = rocket_direct_filesystem();

		$container->addServiceProvider( 'WP_Rocket\Engine\Activation\ServiceProvider' );
	
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

		if ( class_exists( 'WPaaS\Plugin' ) ) {
			require WP_ROCKET_3RD_PARTY_PATH . 'hosting/godaddy.php';
		}
		if ( defined( 'O2SWITCH_VARNISH_PURGE_KEY' ) ) {
			require WP_ROCKET_3RD_PARTY_PATH . 'hosting/o2switch.php';
		}

		/**
		 * WP Rocket activation.
		 *
		 * @since  3.1.5
		 * @author Grégory Viguier
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

		// Create advanced-cache.php file.
		rocket_generate_advanced_cache_file( new AdvancedCache( WP_ROCKET_PATH . 'views/cache/', $filesystem ) );

		// Update customer key & licence.
		wp_remote_get(
			WP_ROCKET_WEB_API . 'activate-licence.php',
			[
				'blocking' => false,
			]
		);

		wp_remote_get(
			home_url(),
			[
				'timeout'    => 0.01,
				'blocking'   => false,
				'user-agent' => 'WP Rocket/Homepage Preload',
				'sslverify'  => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			]
		);
	}
}
