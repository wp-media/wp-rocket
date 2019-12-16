<?php

spl_autoload_register(
	function( $class ) {
		$rocket_path    = WP_ROCKET_PATH;
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
