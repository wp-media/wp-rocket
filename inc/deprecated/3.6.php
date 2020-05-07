<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * Class aliases.
 */
class_alias( '\WP_Rocket\ThirdParty\Plugins\Smush', '\WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber' );

/**
 * Generate the content of advanced-cache.php file.
 *
 * @since 3.6 deprecated
 * @since 3.5.5 Uses rocket_get_constant() for constants.
 * @since 2.1   Add filter rocket_advanced_cache_file.
 * @since 2.0.3
 *
 * @return  string  $buffer The content of avanced-cache.php file
 */
function get_rocket_advanced_cache_file() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
    _deprecated_function( __FUNCTION__ . '()', '3.6', '\WP_Rocket\Engine\Cache\AdvancedCache::get_advanced_cache_content()' );

	$buffer  = "<?php\n";
	$buffer .= "defined( 'ABSPATH' ) || exit;\n\n";

	// Add a constant to be sure this is our file.
	$buffer .= "define( 'WP_ROCKET_ADVANCED_CACHE', true );\n\n";

	$buffer .= "if ( ! defined( 'WP_ROCKET_CONFIG_PATH' ) ) {\n";
	$buffer .= "\tdefine( 'WP_ROCKET_CONFIG_PATH',       WP_CONTENT_DIR . '/wp-rocket-config/' );\n";
	$buffer .= "}\n\n";

	// Include the Mobile Detect class if we have to create a different caching file for mobile.
	if ( is_rocket_generate_caching_mobile_files() ) {
		$vendor_path = rocket_get_constant( 'WP_ROCKET_VENDORS_PATH' );

		$buffer .= "if ( file_exists( '" . $vendor_path . "classes/class-rocket-mobile-detect.php' ) && ! class_exists( 'Rocket_Mobile_Detect' ) ) {\n";
		$buffer .= "\tinclude_once '" . $vendor_path . "classes/class-rocket-mobile-detect.php';\n";
		$buffer .= "}\n\n";
	}

	// Register a class autoloader and include the process file.
	$buffer .= "if ( version_compare( phpversion(), '" . rocket_get_constant( 'WP_ROCKET_PHP_VERSION' ) . "' ) >= 0 ) {\n\n";

	// Class autoloader.
	$autoloader = rocket_direct_filesystem()->get_contents( rocket_get_constant( 'WP_ROCKET_INC_PATH' ) . 'process-autoloader.php' );

	if ( $autoloader ) {
		$autoloader = preg_replace( '@^<\?php\s*@', '', $autoloader );
		$autoloader = str_replace( [ "\n", "\n\t\n" ], [ "\n\t", "\n\n" ], trim( $autoloader ) );
		$autoloader = str_replace( 'WP_ROCKET_PATH', "'" . rocket_get_constant( 'WP_ROCKET_PATH' ) . "'", $autoloader );

		$buffer .= "\t$autoloader\n\n";
	}

	// Initialize the Cache class and process.
	$buffer .= "\t" . 'if ( ! class_exists( \'\WP_Rocket\Buffer\Cache\' ) ) {
		if ( ! defined( \'DONOTROCKETOPTIMIZE\' ) ) {
			define( \'DONOTROCKETOPTIMIZE\', true ); // WPCS: prefix ok.
		}
		return;
	}

	$rocket_config_class = new \WP_Rocket\Buffer\Config(
		[
			\'config_dir_path\' => \'' . rocket_get_constant( 'WP_ROCKET_CONFIG_PATH' ) . '\',
		]
	);

	( new \WP_Rocket\Buffer\Cache(
		new \WP_Rocket\Buffer\Tests(
			$rocket_config_class
		),
		$rocket_config_class,
		[
			\'cache_dir_path\' => \'' . rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) . '\',
		]
	) )->maybe_init_process();' . "\n";
	$buffer .= "} else {\n";
	// Add a constant to provent include issue.
	$buffer .= "\tdefine( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM', true );\n";
	$buffer .= "}\n";

	/**
	 * Filter the content of advanced-cache.php file.
	 *
	 * @since 2.1
	 *
	 * @param string $buffer The content that will be printed in advanced-cache.php.
	*/
	return (string) apply_filters( 'rocket_advanced_cache_file', $buffer );
}

/**
 * This warning is displayed when the advanced-cache.php file isn't writeable
 *
 * @since 3.6 deprecated
 * @since 2.0
 */
function rocket_warning_advanced_cache_permissions() {
    _deprecated_function( __FUNCTION__ . '()', '3.6', '\WP_Rocket\Engine\Cache\AdvancedCache::notice_permissions()' );

	$advanced_cache_file = WP_CONTENT_DIR . '/advanced-cache.php';

	if ( current_user_can( 'rocket_manage_options' )
		&& ! rocket_direct_filesystem()->is_writable( $advanced_cache_file )
		&& ( ! defined( 'WP_ROCKET_ADVANCED_CACHE' ) || ! WP_ROCKET_ADVANCED_CACHE )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = rocket_notice_writing_permissions( basename( WP_CONTENT_DIR ) . '/advanced-cache.php' );

		rocket_notice_html(
			[
				'status'           => 'error',
				'dismissible'      => '',
				'message'          => $message,
				'dismiss_button'   => __FUNCTION__,
				'readonly_content' => get_rocket_advanced_cache_file(),
			]
		);
	}
}

/**
 * This warning is displayed when the advanced-cache.php file isn't ours
 *
 * @since 3.6 Deprecated
 * @since 2.2
 */
function rocket_warning_advanced_cache_not_ours() {
    _deprecated_function( __FUNCTION__ . '()', '3.6', '\WP_Rocket\Engine\Cache\AdvancedCache::notice_content_not_ours()' );

	if ( ! ( 'plugins.php' === $GLOBALS['pagenow'] && isset( $_GET['activate'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		&& current_user_can( 'rocket_manage_options' )
		&& ! defined( 'WP_ROCKET_ADVANCED_CACHE' )
		&& ( defined( 'WP_CACHE' ) && WP_CACHE )
		&& get_rocket_option( 'version' ) === WP_ROCKET_VERSION
		&& rocket_valid_key() ) {

			$message = rocket_notice_writing_permissions( basename( WP_CONTENT_DIR ) . '/advanced-cache.php' );

			rocket_notice_html(
				[
					'status'      => 'error',
					'dismissible' => '',
					'message'     => $message,
				]
			);
	}
}