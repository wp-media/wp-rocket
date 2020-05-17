<?php
defined( 'ABSPATH' ) || exit;

define( 'WP_ROCKET_ADVANCED_CACHE', true );

if ( ! defined( 'WP_ROCKET_CONFIG_PATH' ) ) {
	define( 'WP_ROCKET_CONFIG_PATH',       WP_CONTENT_DIR . '/wp-rocket-config/' );
}

if ( version_compare( phpversion(), '5.6' ) >= 0 ) {

	if ( ! class_exists( '\WP_Rocket\Buffer\Cache' ) ) {
		if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
			define( 'DONOTROCKETOPTIMIZE', true ); // WPCS: prefix ok.
		}
		return;
	}

	$rocket_config_class = new \WP_Rocket\Buffer\Config(
		[
			'config_dir_path' => 'vfs://public/wp-content/wp-rocket-config/',
		]
	);

	( new \WP_Rocket\Buffer\Cache(
		new \WP_Rocket\Buffer\Tests(
			$rocket_config_class
		),
		$rocket_config_class,
		[
			'cache_dir_path' => 'vfs://public/wp-content/cache/wp-rocket/',
		]
	) )->maybe_init_process();
} else {
	define( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM', true );
}
