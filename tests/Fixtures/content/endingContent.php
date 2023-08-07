

spl_autoload_register(
	function( $class ) use ( $rocket_path ) {
		$rocket_classes = [
			'WP_Rocket\\Buffer\\Abstract_Buffer' => [ $rocket_path . 'inc/classes/Buffer/class-abstract-buffer.php' ],
			'WP_Rocket\\Buffer\\Cache'           => [ $rocket_path . 'inc/classes/Buffer/class-cache.php' ],
			'WP_Rocket\\Buffer\\Tests'           => [ $rocket_path . 'inc/classes/Buffer/class-tests.php' ],
			'WP_Rocket\\Buffer\\Config'          => [ $rocket_path . 'inc/classes/Buffer/class-config.php' ],
			'WP_Rocket\\Logger\\HTMLFormatter'   => [
				$rocket_path . 'inc/Logger/HTMLFormatter.php',
				$rocket_path . 'inc/classes/logger/class-html-formatter.php',
			],
			'WP_Rocket\\Logger\\Logger'          => [
				$rocket_path . 'inc/Logger/Logger.php',
				$rocket_path . 'inc/classes/logger/class-logger.php',
			],
			'WP_Rocket\\Logger\\StreamHandler'   => [
				$rocket_path . 'inc/Logger/StreamHandler.php',
				$rocket_path . 'inc/classes/logger/class-stream-handler.php',
			],
			'WP_Rocket\\Traits\\Memoize'         => [ $rocket_path . 'inc/classes/traits/trait-memoize.php' ],
		];

		if ( isset( $rocket_classes[ $class ] ) ) {
			$file_options = $rocket_classes[ $class ];
			$file         = '';

			foreach ( $file_options as $file_option ) {
				if ( file_exists( $file_option ) ) {
					$file = $file_option;
					break;
				}
			}
		} elseif ( strpos( $class, 'WP_Rocket\\Dependencies\\Monolog\\' ) === 0 ) {
			$file = $rocket_path . 'inc/Dependencies/Monolog/' . str_replace( '\\', '/', $class ) . '.php';
			if ( ! file_exists( $file ) ) {
				$file = $rocket_path . 'vendor/monolog/monolog/src/' . str_replace( '\\', '/', $class ) . '.php';
			}
		} elseif ( strpos( $class, 'WP_Rocket\\Dependencies\\Psr\\Log\\' ) === 0 ) {
			$file = $rocket_path . 'inc/Dependencies/Psr/Log/' . str_replace( '\\', '/', $class ) . '.php';
			if ( ! file_exists( $file ) ) {
				$file = $rocket_path . 'vendor/psr/log/' . str_replace( '\\', '/', $class ) . '.php';
			}
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
		define( 'DONOTROCKETOPTIMIZE', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
	}
	return;
}

$rocket_config_class = new Config(
	[
		'config_dir_path' => $rocket_config_path,
	]
);

( new Cache(
	new Tests(
		$rocket_config_class
	),
	$rocket_config_class,
	[
		'cache_dir_path' => $rocket_cache_path,
	]
) )->maybe_init_process();
