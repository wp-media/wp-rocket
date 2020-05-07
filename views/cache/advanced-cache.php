<?php

use WP_Rocket\Buffer\Cache;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;

defined( 'ABSPATH' ) || exit;

define( 'WP_ROCKET_ADVANCED_CACHE', true );

if ( ! defined( 'WP_ROCKET_CONFIG_PATH' ) ) {
	define( 'WP_ROCKET_CONFIG_PATH', WP_CONTENT_DIR . '/wp-rocket-config/' );
}

if ( file_exists( '{{VENDOR_PATH}}classes/class-rocket-mobile-detect.php' ) && ! class_exists( 'Rocket_Mobile_Detect' ) ) {
	include_once '{{VENDOR_PATH}}classes/class-rocket-mobile-detect.php';
}

if ( version_compare( phpversion(), '{{WP_ROCKET_VERSION}}' ) >= 0 ) {
	spl_autoload_register(
		function( $class ) {
			$rocket_path    = '{{WP_ROCKET_PATH}}';
			$rocket_classes = [
				'WP_Rocket\\Buffer\\Abstract_Buffer' => $rocket_path . 'inc/classes/Buffer/class-abstract-buffer.php',
				'WP_Rocket\\Buffer\\Cache'           => $rocket_path . 'inc/classes/Buffer/class-cache.php',
				'WP_Rocket\\Buffer\\Tests'           => $rocket_path . 'inc/classes/Buffer/class-tests.php',
				'WP_Rocket\\Buffer\\Config'          => $rocket_path . 'inc/classes/Buffer/class-config.php',
				'WP_Rocket\\Logger\\HTML_Formatter'  => $rocket_path . 'inc/classes/logger/class-html-formatter.php',
				'WP_Rocket\\Logger\\Logger'          => $rocket_path . 'inc/classes/logger/class-logger.php',
				'WP_Rocket\\Logger\\Stream_Handler'  => $rocket_path . 'inc/classes/logger/class-stream-handler.php',
				'WP_Rocket\\Traits\\Memoize'         => $rocket_path . 'inc/classes/traits/trait-memoize.php',
			];

			if ( isset( $rocket_classes[ $class ] ) ) {
				$file = $rocket_classes[ $class ];
			} elseif ( strpos( $class, 'Monolog\\' ) === 0 ) {
				$file = $rocket_path . 'vendor/monolog/monolog/src/' . str_replace( '\\', '/', $class ) . '.php';
			} elseif ( strpos( $class, 'Psr\\Log\\' ) === 0 ) {
				$file = $rocket_path . 'vendor/psr/log/' . str_replace( '\\', '/', $class ) . '.php';
			} else {
				return;
			}

			if ( file_exists( $file ) ) {
				require $file;
			}
		}
	);

	if ( ! class_exists( '\WP_Rocket\Buffer\Cache' ) ) {
		if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
			define( 'DONOTROCKETOPTIMIZE', true ); // WPCS: prefix ok.
		}
		return;
	}

	$rocket_config_class = new Config(
		[
			'config_dir_path' => '{{WP_ROCKET_CONFIG_PATH}}',
		]
	);

	( new Cache(
		new Tests(
			$rocket_config_class
		),
		$rocket_config_class,
		[
			'cache_dir_path' => '{{WP_ROCKET_CACHE_PATH}}',
		]
	) )->maybe_init_process();
}  else {
	define( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM', true );
}
