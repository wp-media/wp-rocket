<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( ! class_exists( '\WP_Rocket\Buffer\Cache' ) ) {
	// Et paf des chocapics.
	if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
		define( 'DONOTROCKETOPTIMIZE', true );
	}
	return;
}

( new \WP_Rocket\Buffer\Cache(
	[
		'config_dir_path' => $rocket_config_path,
		'cache_dir_path'  => $rocket_cache_path,
	]
) )->maybe_init_process();
