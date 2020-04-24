<?php

$starting = <<<STARTING_CONTENTS
<?php
defined( 'ABSPATH' ) || exit;

define( 'WP_ROCKET_ADVANCED_CACHE', true );

if ( ! defined( 'WP_ROCKET_CONFIG_PATH' ) ) {
	define( 'WP_ROCKET_CONFIG_PATH',       WP_CONTENT_DIR . '/wp-rocket-config/' );
}


STARTING_CONTENTS;

$mobile = <<<MOBILE_CONTENTS
if ( file_exists( 'vfs://public/wp-content/plugins/wp-rocket/inc/vendors/classes/class-rocket-mobile-detect.php' ) && ! class_exists( 'Rocket_Mobile_Detect' ) ) {
	include_once 'vfs://public/wp-content/plugins/wp-rocket/inc/vendors/classes/class-rocket-mobile-detect.php';
}


MOBILE_CONTENTS;

$ending = <<<ENDING_CONTENTS
if ( version_compare( phpversion(), '5.6' ) >= 0 ) {

	spl_autoload_register(
		function( \$class ) {
			\$rocket_path    = 'vfs://public/wp-content/plugins/wp-rocket/';
			\$rocket_classes = [
				'WP_Rocket\\\Buffer\\\Abstract_Buffer' => \$rocket_path . 'inc/classes/Buffer/class-abstract-buffer.php',
				'WP_Rocket\\\Buffer\\\Cache'           => \$rocket_path . 'inc/classes/Buffer/class-cache.php',
				'WP_Rocket\\\Buffer\\\Tests'           => \$rocket_path . 'inc/classes/Buffer/class-tests.php',
				'WP_Rocket\\\Buffer\\\Config'          => \$rocket_path . 'inc/classes/Buffer/class-config.php',
				'WP_Rocket\\\Logger\\\HTML_Formatter'  => \$rocket_path . 'inc/classes/logger/class-html-formatter.php',
				'WP_Rocket\\\Logger\\\Logger'          => \$rocket_path . 'inc/classes/logger/class-logger.php',
				'WP_Rocket\\\Logger\\\Stream_Handler'  => \$rocket_path . 'inc/classes/logger/class-stream-handler.php',
				'WP_Rocket\\\Traits\\\Memoize'         => \$rocket_path . 'inc/classes/traits/trait-memoize.php',
			];

			if ( isset( \$rocket_classes[ \$class ] ) ) {
				\$file = \$rocket_classes[ \$class ];
			} elseif ( strpos( \$class, 'Monolog\\\' ) === 0 ) {
				\$file = \$rocket_path . 'vendor/monolog/monolog/src/' . str_replace( '\\\', '/', \$class ) . '.php';
			} elseif ( strpos( \$class, 'Psr\\\Log\\\' ) === 0 ) {
				\$file = \$rocket_path . 'vendor/psr/log/' . str_replace( '\\\', '/', \$class ) . '.php';
			} else {
				return;
			}

			if ( file_exists( \$file ) ) {
				require \$file;
			}
		}
	);

	if ( ! class_exists( '\WP_Rocket\Buffer\Cache' ) ) {
		if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
			define( 'DONOTROCKETOPTIMIZE', true ); // WPCS: prefix ok.
		}
		return;
	}

	\$rocket_config_class = new \WP_Rocket\Buffer\Config(
		[
			'config_dir_path' => 'vfs://public/wp-content/wp-rocket-config/',
		]
	);

	( new \WP_Rocket\Buffer\Cache(
		new \WP_Rocket\Buffer\Tests(
			\$rocket_config_class
		),
		\$rocket_config_class,
		[
			'cache_dir_path' => 'vfs://public/wp-content/cache/wp-rocket/',
		]
	) )->maybe_init_process();
} else {
	define( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM', true );
}

ENDING_CONTENTS;

$default = $starting . $ending;

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir' => 'wp-content/',

	'structure' => [
		'wp-content'       => [
			'plugins' => [
				'wp-rocket' => [
					'inc'              => [
						'process-autoloader.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'inc/process-autoloader.php' ),
					],
					'licence-data.php' => '',
				],
			],
		],
		'wp-rocket-config' => [
			'example.org.php' => '<?php $var = "Some contents.";',
		],

		'advanced-cache.php' => '<?php $var = "Some contents.";',
	],

	'settings' => [
		'cache_mobile'            => 0,
		'do_caching_mobile_files' => 0,
	],

	'test_data' => [
		[
			'settings'                                => [],
			'expected'                                => $default,
			'is_rocket_generate_caching_mobile_files' => false,
		],
		[
			'settings'                                => [
				'cache_mobile' => 1,
			],
			'expected'                                => $default,
			'is_rocket_generate_caching_mobile_files' => false,
		],
		[
			'settings'                                => [
				'do_caching_mobile_files' => 1,
			],
			'expected'                                => $default,
			'is_rocket_generate_caching_mobile_files' => false,
		],
		[
			'settings'                                => [
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
			],
			'expected'                                => $starting . $mobile . $ending,
			'is_rocket_generate_caching_mobile_files' => true,
		],
	],
];
