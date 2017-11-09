<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * This warnings are displayed when the plugin can not be deactivated correctly
 *
 * @since 2.0.0
 */
function rocket_bad_deactivations() {
	global $current_user;

	$msgs = get_transient( $current_user->ID . '_donotdeactivaterocket' );
	// This filter is documented in inc/admin-bar.php.
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) && $msgs ) {

		delete_transient( $current_user->ID . '_donotdeactivaterocket' );
		$errors = array();

		foreach ( $msgs as $msg ) {
			switch ( $msg ) {
				case 'wpconfig':
					$errors['wpconfig'] = '<p>' . sprintf(
						// translators: %1$s WP Rocket plugin name; %2$s = wp-config.php.
						__( '<strong>%1$s</strong> has not been deactivated due to missing writing permissions.<br>
Make <st>%2$s</strong> writeable and retry deactivation, or force deactivation now.', 'rocket' ),
						WP_ROCKET_PLUGIN_NAME,
						'wp-config.php'
					) . '</p>';
					break;

				case 'htaccess':
					$errors['htaccess'] = '<p>' . sprintf(
						// translators: %1$s WP Rocket plugin name; %2$s = .htaccess.
						__( '<strong>%1$s</strong> has not been deactivated due to missing writing permissions.<br>
Make <st>%2$s</strong> writeable and retry deactivation, or force deactivation now.', 'rocket' ),
						WP_ROCKET_PLUGIN_NAME,
						'.htaccess'
					) . '</p>';
					break;
			}

			/**
			 * Filter the output messages for each bad deactivation attempt.
			 *
			 * @since 2.0.0
			 *
			 * @param array $errors Contains the error messages to be filtered
			 * @param string $msg Contains the error type (wpconfig or htaccess)
			 */
			$errors = apply_filters( 'rocket_bad_deactivations', $errors, $msg );

		}

		rocket_notice_html( array(
			'status'      => 'error',
			'dismissible' => '',
			'message'     => implode( '', $errors ),
			'action'      => 'force_deactivation',
		) );
	}
}
add_action( 'admin_notices', 'rocket_bad_deactivations' );

/**
 * This warning is displayed to inform the user that a plugin de/activation can be followed by a cache clear
 *
 * @since 1.3.0
 */
function rocket_warning_plugin_modification() {
	// This filter is documented in inc/admin-bar.php.
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) && rocket_valid_key() ) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		rocket_notice_html( array(
			'status'         => 'warning',
			'dismissible'    => '',
			// translators: %s is WP Rocket plugin name (maybe white label).
			'message'        => sprintf( __( '<strong>%s</strong>: One or more extensions have been enabled or disabled, clear the cache if necessary.', 'rocket' ), WP_ROCKET_PLUGIN_NAME ),
			'action'         => 'clear_cache',
			'dismiss_button' => true,
		) );
	}
}
add_action( 'admin_notices', 'rocket_warning_plugin_modification' );

/**
 * This warning is displayed when some plugins may conflict with WP Rocket
 *
 * @since 1.3.0
 */
function rocket_plugins_to_deactivate() {
	$plugins_to_deactivate = array();

	// Deactivate all plugins who can cause conflicts with WP Rocket.
	$plugins = array(
		'w3-total-cache'                             => 'w3-total-cache/w3-total-cache.php',
		'wp-super-cache'                             => 'wp-super-cache/wp-cache.php',
		'quick-cache'                                => 'quick-cache/quick-cache.php',
		'hyper-cache'                                => 'hyper-cache/plugin.php',
		'hyper-cache-extended'                       => 'hyper-cache-extended/plugin.php',
		'wp-fast-cache'                              => 'wp-fast-cache/wp-fast-cache.php',
		'flexicache'                                 => 'flexicache/wp-plugin.php',
		'wp-fastest-cache'                           => 'wp-fastest-cache/wpFastestCache.php',
		'lite-cache'                                 => 'lite-cache/plugin.php',
		'gator-cache'                                => 'gator-cache/gator-cache.php',
		'wp-http-compression'                        => 'wp-http-compression/wp-http-compression.php',
		'wordpress-gzip-compression'                 => 'wordpress-gzip-compression/ezgz.php',
		'gzip-ninja-speed-compression'               => 'gzip-ninja-speed-compression/gzip-ninja-speed.php',
		'speed-booster-pack'                         => 'speed-booster-pack/speed-booster-pack.php',
		'wp-performance-score-booster'               => 'wp-performance-score-booster/wp-performance-score-booster.php',
		'remove-query-strings-from-static-resources' => 'remove-query-strings-from-static-resources/remove-query-strings.php',
		'query-strings-remover'                      => 'query-strings-remover/query-strings-remover.php',
		'wp-ffpc'                                    => 'wp-ffpc/wp-ffpc.php',
		'far-future-expiry-header'                   => 'far-future-expiry-header/far-future-expiration.php',
		'combine-css'                                => 'combine-css/combine-css.php',
		'super-static-cache'                         => 'super-static-cache/super-static-cache.php',
		'wpcompressor'                               => 'wpcompressor/wpcompressor.php',
		'check-and-enable-gzip-compression'          => 'check-and-enable-gzip-compression/richards-toolbox.php',
		'leverage-browser-caching-ninjas'            => 'leverage-browser-caching-ninjas/leverage-browser-caching-ninja.php',
		'force-gzip'                                 => 'force-gzip/force-gzip.php',
	);

	if ( get_rocket_option( 'lazyload' ) ) {
		$plugins['bj-lazy-load']              = 'bj-lazy-load/bj-lazy-load.php';
		$plugins['lazy-load']                 = 'lazy-load/lazy-load.php';
		$plugins['jquery-image-lazy-loading'] = 'jquery-image-lazy-loading/jq_img_lazy_load.php';
		$plugins['advanced-lazy-load']        = 'advanced-lazy-load/advanced_lazyload.php';
		$plugins['crazy-lazy']                = 'crazy-lazy/crazy-lazy.php';
	}

	if ( get_rocket_option( 'lazyload_iframes' ) ) {
		$plugins['lazy-load-for-videos'] = 'lazy-load-for-videos/codeispoetry.php';
	}

	if ( get_rocket_option( 'minify_css' ) || get_rocket_option( 'minify_js' ) || get_rocket_option( 'minify_html' ) ) {
		$plugins['bwp-minify']              = 'bwp-minify/bwp-minify.php';
		$plugins['wp-minify']               = 'wp-minify/wp-minify.php';
		$plugins['scripts-gzip']            = 'scripts-gzip/scripts_gzip.php';
		$plugins['minqueue']                = 'minqueue/plugin.php';
		$plugins['dependency-minification'] = 'dependency-minification/dependency-minification.php';
	}

	if ( get_rocket_option( 'minify_css' ) || get_rocket_option( 'minify_js' ) ) {
		$plugins['async-js-and-css'] = 'async-js-and-css/asyncJSandCSS.php';
	}

	if ( get_rocket_option( 'minify_html' ) ) {
		$plugins['wp-html-compression'] = 'wp-html-compression/wp-html-compression.php';
		$plugins['wp-compress-html']    = 'wp-compress-html/wp_compress_html.php';
	}

	if ( get_rocket_option( 'minify_js' ) ) {
		$plugins['wp-js']                = 'wp-js/wp-js.php';
		$plugins['combine-js']           = 'combine-js/combine-js.php';
		$plugins['footer-javascript']    = 'footer-javascript/footer-javascript.php';
		$plugins['scripts-to-footerphp'] = 'scripts-to-footerphp/scripts-to-footer.php';
	}

	if ( get_rocket_option( 'do_cloudflare' ) ) {
		$plugins['cloudflare'] = 'cloudflare/cloudflare.php';
	}

	/**
	 * Filter the recommended plugins to deactivate to prevent conflicts
	 *
	 * @since 2.6.4
	 *
	 * @param string $plugins List of recommended plugins to deactivate
	*/
	$plugins = apply_filters( 'rocket_plugins_to_deactivate', $plugins );

	$plugins = array_filter( $plugins, 'is_plugin_active' );

	// This filter is documented in inc/admin-bar.php.
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& count( $plugins )
		&& rocket_valid_key()
	) {

		// translators: %s is WP Rocket plugin name (maybe white label).
		$warning = '<p>' . sprintf( __( '<strong>%s</strong>: The following plugins are not compatible with this plugin and may cause unexpected results:', 'rocket' ), WP_ROCKET_PLUGIN_NAME ) . '</p>';

		$warning .= '<ul class="rocket-plugins-error">';

		foreach ( $plugins as $plugin ) {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin );
			$warning .= '<li>' . $plugin_data['Name'] . '</span> <a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=deactivate_plugin&plugin=' . rawurlencode( $plugin ) ), 'deactivate_plugin' ) . '" class="button-secondary alignright">' . __( 'Deactivate', 'rocket' ) . '</a></li>';
		}
	
		$warning .= '</ul>';

		rocket_notice_html( array(
			'status'      => 'error',
			'dismissible' => '',
			'message'     => $warning,
		) );
	}
}
add_action( 'admin_notices', 'rocket_plugins_to_deactivate' );

/**
 * This warning is displayed when there is no permalink structure in the configuration.
 *
 * @since 1.0
 */
function rocket_warning_using_permalinks() {
	// This filter is documented in inc/admin-bar.php.
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ! $GLOBALS['wp_rewrite']->using_permalinks()
		&& rocket_valid_key()
	) {
		$warning = sprintf(
				// translators: %1$s WP Rocket plugin name; %2$s = permalink settings admin URL.
				__( '<strong>%1$s</strong>: A custom permalink structure is required for the plugin to work properly. <a href="%2$s">Go to permalinks settings</a>', 'rocket' ),
				WP_ROCKET_PLUGIN_NAME,
				admin_url( 'options-permalink.php' )
			);

		rocket_notice_html( array(
			'status'      => 'error',
			'dismissible' => '',
			'message'     => $warning,
		) );
	}
}
add_action( 'admin_notices', 'rocket_warning_using_permalinks' );

/**
 * This warning is displayed when the wp-config.php file isn't writable
 *
 * @since 2.0
 */
function rocket_warning_wp_config_permissions() {
	$config_file = rocket_find_wpconfig_path();

	if ( ! ( 'plugins.php' === $GLOBALS['pagenow'] && isset( $_GET['activate'] ) )
		// This filter is documented in inc/admin-bar.php.
		&& current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ( ! rocket_direct_filesystem()->is_writable( $config_file ) && ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$warning = '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>: ' .
			sprintf(
				// translators: %1$s WP Rocket plugin name (maybe white label); %2$s = concerned file/folder; %3$s = URL.
				__( '<strong>%1$s</strong>: cannot configure itself due to missing writing permissions. Affected file:<br>
				- %2$s<br>
				Troubleshoot: <a href="%3$s" target="_blank">Resolving issues with writing permissions</a>', 'rocket' ),
				WP_ROCKET_PLUGIN_NAME,
				'wp-config.php',
				'https://codex.wordpress.org/Changing_File_Permissions'
			);

		rocket_notice_html( array(
			'status' => 'error',
			'dismissible' => '',
			'message' => $warning,
			'dismiss_button' => true,
			'readonly_content' => "/** Enable Cache by " . WP_ROCKET_PLUGIN_NAME . " */\r\ndefine( 'WP_CACHE', true );\r\n",
		) );
	}
}
add_action( 'admin_notices', 'rocket_warning_wp_config_permissions' );

/**
 * This warning is displayed when the advanced-cache.php file isn't writeable
 *
 * @since 2.0
 */
function rocket_warning_advanced_cache_permissions() {
	$advanced_cache_file = WP_CONTENT_DIR . '/advanced-cache.php';

	// This filter is documented in inc/admin-bar.php.
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ! rocket_direct_filesystem()->is_writable( $advanced_cache_file )
		&& ( ! defined( 'WP_ROCKET_ADVANCED_CACHE' ) || ! WP_ROCKET_ADVANCED_CACHE )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$warning = '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>: ' .
			sprintf(
				// translators: %1$s WP Rocket plugin name (maybe white label); %2$s = concerned file/folder; %3$s = URL.
				__( '<strong>%1$s</strong> cannot configure itself due to missing writing permissions. Affected file:<br>
					- %2$s<br>
					Troubleshoot: <a href="%3$s" target="_blank">Resolving issues with writing permissions</a>', 'rocket' ),
					WP_ROCKET_PLUGIN_NAME,
					basename( WP_CONTENT_DIR ) . '/advanced-cache.php',
					'https://codex.wordpress.org/Changing_File_Permissions'
			);
	
		rocket_notice_html( array(
			'status'           => 'error',
			'dismissible'      => '',
			'message'          => $warning,
			'dismiss_button'   => true,
			'readonly_content' => get_rocket_advanced_cache_file(),
		) );
	}
}
add_action( 'admin_notices', 'rocket_warning_advanced_cache_permissions' );

/**
 * This warning is displayed when the advanced-cache.php file isn't ours
 *
 * @since 2.2
 */
function rocket_warning_advanced_cache_not_ours() {
	// This filter is documented in inc/admin-bar.php.
	if ( ! ( 'plugins.php' === $GLOBALS['pagenow'] && isset( $_GET['activate'] ) )
		&& current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ! defined( 'WP_ROCKET_ADVANCED_CACHE' )
		&& ( defined( 'WP_CACHE' ) && WP_CACHE )
		&& get_rocket_option( 'version' ) === WP_ROCKET_VERSION
		&& rocket_valid_key() ) {
			$warning = '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>: ' .
			sprintf(
				// translators: %1$s WP Rocket plugin name (maybe white label); %2$s = concerned file/folder; %3$s = URL.
				__( '<strong>%1$s</strong>: cannot configure itself due to missing writing permissions. Affected file:<br>
				- %2$s<br>
				Troubleshoot: <a href="%3$s" target="_blank">Resolving issues with writing permissions</a>', 'rocket' ),
				WP_ROCKET_PLUGIN_NAME,
				basename( WP_CONTENT_DIR ) . '/advanced-cache.php',
				'https://codex.wordpress.org/Changing_File_Permissions'
			);

			rocket_notice_html( array(
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $warning,
			) );
	}
}
add_action( 'admin_notices', 'rocket_warning_advanced_cache_not_ours' );

/**
 * This warning is displayed when the .htaccess file doesn't exist or isn't writeable
 *
 * @since 1.0
 */
function rocket_warning_htaccess_permissions() {
	global $is_apache;
	$htaccess_file = get_home_path() . '.htaccess';

	// This filter is documented in inc/admin-bar.php.
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ( ! rocket_direct_filesystem()->is_writable( $htaccess_file ) )
		&& $is_apache
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$warning = '<p><strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>: ' .
			sprintf(
				// translators: %1$s WP Rocket plugin name (maybe white label); %2$s = concerned file/folder; %3$s = URL.
				__( '<strong>%1$s</strong>: cannot configure itself due to missing writing permissions. Affected file:<br>
				- %2$s<br>
				Troubleshoot: <a href="%3$s" target="_blank">Resolving issues with writing permissions</a>', 'rocket' ),
				WP_ROCKET_PLUGIN_NAME,
				'.htaccess',
				'https://codex.wordpress.org/Changing_File_Permissions'
			) . '</p>';

		$warning .= '<p>' . sprintf( 
			// translators: %s = WP Rocket name (maybe white label).
			__( 'Here are the rewrite rules you have to put in your <code>.htaccess</code> file for <strong>%s</strong> to work correctly. Click on the field and press Ctrl-A to select all.', 'rocket' ), WP_ROCKET_PLUGIN_NAME
			) . '<br>' . __( '<strong>Warning:</strong> This message will popup again and its content may be updated when saving the options', 'rocket' ) . '</p>';

		rocket_notice_html( array(
			'status'           => 'error',
			'dismissible'      => '',
			'message'          => $warning,
			'dismiss_button'   => true,
			'readonly_content' => get_rocket_htaccess_marker(),
		) );
	}
}
add_action( 'admin_notices', 'rocket_warning_htaccess_permissions' );

/**
 * This warning is displayed when the config dir isn't writeable
 *
 * @since 2.0.2
 */
function rocket_warning_config_dir_permissions() {
	/** This filter is documented in inc/admin-bar.php */
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ( ! rocket_direct_filesystem()->is_writable( WP_ROCKET_CONFIG_PATH ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$warning = '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>: ' .
			sprintf(
				// translators: %1$s WP Rocket plugin name (maybe white label); %2$s = concerned file/folder; %3$s = URL.
				__( '<strong>%1$s</strong>: cannot configure itself due to missing writing permissions. Affected file:<br>
				- %2$s<br>
				Troubleshoot: <a href="%3$s" target="_blank">Resolving issues with writing permissions</a>', 'rocket' ),
				WP_ROCKET_PLUGIN_NAME,
				trim( str_replace( ABSPATH, '', WP_ROCKET_CONFIG_PATH ), '/' ),
				'https://codex.wordpress.org/Changing_File_Permissions'
			);

		rocket_notice_html( array(
			'status'      => 'error',
			'dismissible' => '',
			'message'     => $warning,
		) );
	}
}
add_action( 'admin_notices', 'rocket_warning_config_dir_permissions' );

/**
 * This warning is displayed when the cache dir isn't writeable
 *
 * @since 1.0
 */
function rocket_warning_cache_dir_permissions() {
	/** This filter is documented in inc/admin-bar.php */
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ( ! rocket_direct_filesystem()->is_writable( WP_ROCKET_CACHE_PATH ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$warning = '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>: ' .
			sprintf(
				// translators: %1$s WP Rocket plugin name (maybe white label); %2$s = concerned file/folder; %3$s = URL.
				__( '<strong>%1$s</strong>: cannot configure itself due to missing writing permissions. Affected file:<br>
				- %2$s<br>
				Troubleshoot: <a href="%3$s" target="_blank">Resolving issues with writing permissions</a>', 'rocket' ),
				WP_ROCKET_PLUGIN_NAME,
				trim( str_replace( ABSPATH, '', WP_ROCKET_CACHE_PATH ), '/' ),
				'https://codex.wordpress.org/Changing_File_Permissions'
			);

		rocket_notice_html( array(
			'status'      => 'error',
			'dismissible' => '',
			'message'     => $warning,
		) );
	}
}
add_action( 'admin_notices', 'rocket_warning_cache_dir_permissions' );

/**
 * This warning is displayed when the minify cache dir isn't writeable
 *
 * @since 2.1
 */
function rocket_warning_minify_cache_dir_permissions() {
	// This filter is documented in inc/admin-bar.php.
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ( ! rocket_direct_filesystem()->is_writable( WP_ROCKET_MINIFY_CACHE_PATH ) )
		&& ( get_rocket_option( 'minify_css', false ) || get_rocket_option( 'minify_js', false ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$warning = '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>: ' .
			sprintf(
				// translators: %1$s WP Rocket plugin name (maybe white label); %2$s = concerned file/folder; %3$s = URL.
				__( '<strong>%1$s</strong>: cannot configure itself due to missing writing permissions. Affected file:<br>
				- %2$s<br>
				Troubleshoot: <a href="%3$s" target="_blank">Resolving issues with writing permissions</a>', 'rocket' ),
				WP_ROCKET_PLUGIN_NAME,
				trim( str_replace( ABSPATH, '', WP_ROCKET_MINIFY_CACHE_PATH ), '/' ),
				'https://codex.wordpress.org/Changing_File_Permissions'
			);

		rocket_notice_html( array(
			'status'      => 'error',
			'dismissible' => '',
			'message'     => $warning,
		) );
	}
}
add_action( 'admin_notices', 'rocket_warning_minify_cache_dir_permissions' );

/**
 * This warning is displayed when the busting cache dir isn't writeable
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_warning_busting_cache_dir_permissions() {
	// This filter is documented in inc/admin-bar.php.
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ( ! rocket_direct_filesystem()->is_writable( WP_ROCKET_CACHE_BUSTING_PATH ) )
		&& ( get_rocket_option( 'remove_query_strings', false ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>: ' .
			sprintf(
				// translators: %1$s WP Rocket plugin name (maybe white label); %2$s = concerned file/folder; %3$s = URL.
				__( '<strong>%1$s</strong>: cannot configure itself due to missing writing permissions. Affected file:<br>
				- %2$s<br>
				Troubleshoot: <a href="%3$s" target="_blank">Resolving issues with writing permissions</a>', 'rocket' ),
				WP_ROCKET_PLUGIN_NAME,
				trim( str_replace( ABSPATH, '', WP_ROCKET_CACHE_BUSTING_PATH ), '/' ),
				'https://codex.wordpress.org/Changing_File_Permissions'
			);

		rocket_notice_html( array(
			'status'      => 'error',
			'dismissible' => '',
			'message'     => $message,
		) );
	}
}
add_action( 'admin_notices', 'rocket_warning_busting_cache_dir_permissions' );

/**
 * This thankful message is displayed when the site has been added
 *
 * @since 2.2
 */
function rocket_thank_you_license() {
	if ( '1' === get_rocket_option( 'license' ) ) {
		$options = get_option( WP_ROCKET_SLUG );
		$options['license'] = time();
		$options['ignore'] = true;
		update_option( WP_ROCKET_SLUG, $options );

		rocket_notice_html( array(
			// translators: %s = plugin name (maybe white label).
			'message'     => sprintf( __( '%s: is good to go!', 'rocket' ), '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>' ),
		) );
	}
}
add_action( 'admin_notices', 'rocket_thank_you_license' );

/**
 * Add a message about Imagify on the "Upload New Media" screen and WP Rocket options page.
 *
 * @since 2.7
 */
function rocket_imagify_notice() {
	$current_screen = get_current_screen();

	// Add the notice only on the "WP Rocket" settings, "Media Library" & "Upload New Media" screens.
	if ( 'admin_notices' === current_filter() && ( isset( $current_screen ) && 'settings_page_wprocket' !== $current_screen->base ) ) {
		return;
	}

	$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

	if ( defined( 'IMAGIFY_VERSION' ) || in_array( __FUNCTION__, (array) $boxes, true ) || 1 === get_option( 'wp_rocket_dismiss_imagify_notice' ) || rocket_is_white_label() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$imagify_plugin = 'imagify/imagify.php';
	$is_imagify_installed = rocket_is_plugin_installed( $imagify_plugin );

	$action_url = $is_imagify_installed ?
	rocket_get_plugin_activation_link( $imagify_plugin )
		:
	wp_nonce_url( add_query_arg(
		array(
			'action'       => 'install-plugin',
			'plugin'    => 'imagify',
		),
		admin_url( 'update.php' )
	), 'install-plugin_imagify' );

	$details_url = add_query_arg(
		array(
			'tab'       => 'plugin-information',
			'plugin'    => 'imagify',
			'TB_iframe' => true,
			'width'     => 722,
			'height'    => 949,
		),
		admin_url( 'plugin-install.php' )
	);

	$classes = $is_imagify_installed ? '' : ' install-now';
	$cta_txt = $is_imagify_installed ? esc_html__( 'Activate Imagify', 'rocket' ) : esc_html__( 'Install Imagify for Free', 'rocket' );

	$dismiss_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=rocket_ignore&box=' . __FUNCTION__ ),
		'rocket_ignore_' . __FUNCTION__
	);
	?>

	<div id="plugin-filter" class="updated plugin-card plugin-card-imagify rkt-imagify-notice">
		<a href="<?php echo $dismiss_url; ?>" class="rkt-cross"><span class="dashicons dashicons-no"></span></a>

		<p class="rkt-imagify-logo">
			<img src="<?php echo WP_ROCKET_ADMIN_UI_IMG_URL; ?>logo-imagify.png" srcset="<?php echo WP_ROCKET_ADMIN_UI_IMG_URL; ?>logo-imagify.svg 2x" alt="Imagify" width="150" height="18">
		</p>
		<p class="rkt-imagify-msg">
			<?php _e( 'Speed up your website and boost your SEO by reducing image file sizes without losing quality with Imagify.', 'rocket' ); ?>
		</p>
		<p class="rkt-imagify-cta">
			<a data-slug="imagify" href="<?php echo $action_url; ?>" class="button button-primary<?php echo $classes; ?>"><?php echo $cta_txt; ?></a>
			<?php if ( ! $is_imagify_installed ) : ?>
			<br><a data-slug="imagify" data-name="Imagify Image Optimizer" class="thickbox open-plugin-details-modal" href="<?php echo $details_url; ?>"><?php _e( 'More details', 'rocket' ); ?></a>
			<?php endif; ?>
		</p>
	</div>

	<?php
}
add_action( 'admin_notices', 'rocket_imagify_notice' );

/**
 * This notice is displayed after purging the CloudFlare cache
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_cloudflare_purge_result() {
	global $current_user;
	// This filter is documented in inc/admin-bar.php.
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		return;
	}

	if ( ! is_admin() ) {
		return;
	}

	$notice = get_transient( $current_user->ID . '_cloudflare_purge_result' );
	if ( ! $notice ) {
		return;
	}

	delete_transient( $current_user->ID . '_cloudflare_purge_result' );

	rocket_notice_html( array(
		'status'  => $notice['result'],
		'message' => $notice['message'],
	) );
}
add_action( 'admin_notices', 'rocket_cloudflare_purge_result' );

/**
 * This notice is displayed after modifying the CloudFlare settings
 *
 * @since 2.9
 * @author Remy Perona
 */
function rocket_cloudflare_update_settings() {
	global $current_user;
	$screen              = get_current_screen();
	$rocket_wl_name      = get_rocket_option( 'wl_plugin_name', null );
	$wp_rocket_screen_id = isset( $rocket_wl_name ) ? 'settings_page_' . sanitize_key( $rocket_wl_name ) : 'settings_page_wprocket';
	// This filter is documented in inc/admin-bar.php.
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		return;
	}

	if ( $screen->id !== $wp_rocket_screen_id ) {
		return;
	}

	$notices = get_transient( $current_user->ID . '_cloudflare_update_settings' );
	if ( $notices ) {
		$errors = '';
		$success = '';
		delete_transient( $current_user->ID . '_cloudflare_update_settings' );
		foreach ( $notices as $notice ) {
			if ( 'error' === $notice['result'] ) {
				$errors .= $notice['message'] . '<br>';
			} elseif ( 'success' === $notice['result'] ) {
				$success .= $notice['message'] . '<br>';
			}
		}

		if ( ! empty( $success ) ) {
			rocket_notice_html( array(
				'message' => $success,
			) );
		}

		if ( ! empty( $errors ) ) {
			rocket_notice_html( array(
				'status'  => 'error',
				'message' => $success,
			) );
		}
	}
}
add_action( 'admin_notices', 'rocket_cloudflare_update_settings' );

/**
 * Displays a notice for analytics opt-in
 *
 * @since 2.11
 * @author Remy Perona
 */
function rocket_analytics_optin_notice() {
	if ( rocket_is_white_label() ) {
		return;
	}

	$screen              = get_current_screen();
	$rocket_wl_name      = get_rocket_option( 'wl_plugin_name', null );
	$wp_rocket_screen_id = isset( $rocket_wl_name ) ? 'settings_page_' . sanitize_key( $rocket_wl_name ) : 'settings_page_wprocket';
	// This filter is documented in inc/admin-bar.php.
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		return;
	}

	if ( $screen->id !== $wp_rocket_screen_id ) {
		return;
	}

	if ( 1 === (int) get_option( 'rocket_analytics_notice_displayed' ) ) {
		return;
	}

	if ( get_rocket_option( 'analytics_enabled' ) ) {
		return;
	}

	$analytics_notice = '<strong>' . __( 'Allow WP Rocket to collect non-sensitive diagnostic data from this website?', 'rocket' ) . '</strong></p>
		<p>' .  __( 'This would enable us to improve WP Rocket for you in the future.', 'rocket' ) . '</p>
		<p><button class="hide-if-no-js button-rocket-reveal rocket-preview-analytics-data">' . __( 'See a preview of which data would be collected', 'rocket' ) . '</button></p>
		<div class="rocket-analytics-data-container">' . rocket_preview_data_collected_list() . '</div>
		<p><a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=rocket_analytics_optin&value=yes' ), 'analytics_optin' ) . '" class="button button-primary">' . __( 'Yes I Allow', 'rocket' ) . '</a> <a href="' .  wp_nonce_url( admin_url( 'admin-post.php?action=rocket_analytics_optin&value=no' ), 'analytics_optin' ) . '" class="button button-secondary">' . __( 'No Thanks', 'rocket' ) . '</a>';

	rocket_notice_html( array(
		'message' => $analytics_notice,
	) );
}
add_action( 'admin_notices', 'rocket_analytics_optin_notice' );

/**
 * Displays a notice after analytics opt-in
 *
 * @since 2.11
 * @author Remy Perona
 */
function rocket_analytics_optin_thankyou_notice() {
	if ( rocket_is_white_label() ) {
		return;
	}

	$screen              = get_current_screen();
	$rocket_wl_name      = get_rocket_option( 'wl_plugin_name', null );
	$wp_rocket_screen_id = isset( $rocket_wl_name ) ? 'settings_page_' . sanitize_key( $rocket_wl_name ) : 'settings_page_wprocket';
	// This filter is documented in inc/admin-bar.php.
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		return;
	}

	if ( $screen->id !== $wp_rocket_screen_id ) {
		return;
	}

	$analytics_optin = get_transient( 'rocket_analytics_optin' );

	if ( ! $analytics_optin ) {
		return;
	}

	$thankyou_message = '<strong>' . __( 'Thank you!', 'rocket' ) . '</strong></p>
		<p>' . __( 'WP Rocket now collects these metrics from your website:', 'rocket' ) . '</p>
		<div>' . rocket_preview_data_collected_list() . '</div>
		<p>' . __( 'If you ever want to opt-out, you can do so from the Tools tab of WP Rocket settings', 'rocket' );

	rocket_notice_html( array(
		'message' => $thankyou_message,
	) );

	delete_transient( 'rocket_analytics_optin' );
}
add_action( 'admin_notices', 'rocket_analytics_optin_thankyou_notice' );

/**
 * Displays a notice after clearing the cache
 *
 * @since 2.11
 * @author Remy Perona
 */
function rocket_clear_cache_notice() {
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		return;
	}

	$cleared_cache = get_transient( 'rocket_clear_cache' );

	if ( ! $cleared_cache ) {
		return;
	}

	delete_transient( 'rocket_clear_cache' );

	switch ( $cleared_cache ) {
		case 'all':
			// translators: %s = WP Rocket name (maybe white label).
			$notice = sprintf( __( '%s cache cleared.', 'rocket' ), WP_ROCKET_PLUGIN_NAME );
			break;
		case 'post':
			$notice = __( 'Post cache cleared.', 'rocket' );
			break;
		case 'term':
			$notice = __( 'Term cache cleared.', 'rocket' );
			break;
		case 'user':
			$notice = __( 'User cache cleared.', 'rocket' );
			break;
		default:
			$notice = '';
			break;
	}

	if ( empty( $notice ) ) {
		return;
	}

	rocket_notice_html( array(
		'message' => $notice,
	) );
}
add_action( 'admin_notices', 'rocket_clear_cache_notice' );

/**
 * This notice is displayed when the sitemap preload is running
 *
 * @since 2.11
 * @author Remy Perona
 */
function rocket_sitemap_preload_running() {
	global $current_user;
	$screen              = get_current_screen();
	$rocket_wl_name      = get_rocket_option( 'wl_plugin_name', null );
	$wp_rocket_screen_id = isset( $rocket_wl_name ) ? 'settings_page_' . sanitize_key( $rocket_wl_name ) : 'settings_page_wprocket';
	// This filter is documented in inc/admin-bar.php.
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		return;
	}

	if ( $screen->id !== $wp_rocket_screen_id ) {
		return;
	}

	$running = get_transient( 'rocket_sitemap_preload_running' );
	if ( ! $running ) {
		return;
	}
	
	
	rocket_notice_html( array(
		'message' => __( 'Sitemap-based cache preload is currently running…', 'rocket' ),
	) );
}
add_action( 'admin_notices', 'rocket_sitemap_preload_running' );

/**
 * This notice is displayed after the sitemap preload is complete
 *
 * @since 2.11
 * @author Remy Perona
 */
function rocket_sitemap_preload_complete() {
	global $current_user;
	$screen              = get_current_screen();
	$rocket_wl_name      = get_rocket_option( 'wl_plugin_name', null );
	$wp_rocket_screen_id = isset( $rocket_wl_name ) ? 'settings_page_' . sanitize_key( $rocket_wl_name ) : 'settings_page_wprocket';
	/** This filter is documented in inc/admin-bar.php */
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		return;
	}

	if ( $screen->id !== $wp_rocket_screen_id ) {
		return;
	}

	$result = get_transient( 'rocket_sitemap_preload_complete' );
	if ( ! $result ) {
		return;
	}

	delete_transient( 'rocket_sitemap_preload_complete' );

	rocket_notice_html( array(
		// translators: %d is the number of pages preloaded.
		'message' => sprintf( __( 'Sitemap preload complete: %d pages not yet cached have been preloaded.', 'rocket' ), $result ),
	) );
}
add_action( 'admin_notices', 'rocket_sitemap_preload_complete' );

/**
 * Outputs notice HTML
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @param array $args An array of arguments used to determine the notice output.
 * @return string notice HTML output
 */
function rocket_notice_html( $args ) {
	$defaults = array(
		'status'           => 'success',
		'dismissible'      => 'is-dismissible',
		'message'          => '',
		'action'           => '',
		'dismiss_button'   => false,
		'readonly_content' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	switch ( $args['action'] ) {
		case 'clear_cache':
			$action = '<a class="wp-core-ui button" href="' . wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=all' ), 'purge_cache_all' ) . '">' . __( 'Clear cache', 'rocket' ) . '</a>';
			break;
		case 'force_deactivation':
			/**
			 * Allow a "force deactivation" link to be printed, use at your own risks
			 *
			 * @since 2.0.0
			 *
			 * @param bool $permit_force_deactivation true will print the link.
			 */
			$permit_force_deactivation = apply_filters( 'rocket_permit_force_deactivation', true );

			// We add a link to permit "force deactivation", use at your own risks.
			if ( $permit_force_deactivation ) {
				global $status, $page, $s;
				$plugin_file = 'wp-rocket/wp-rocket.php';
				$rocket_nonce = wp_create_nonce( 'force_deactivation' );

				$action = '<a href="' . wp_nonce_url( 'plugins.php?action=deactivate&amp;rocket_nonce=' . $rocket_nonce . '&amp;plugin=' . $plugin_file . '&amp;plugin_status=' . $status . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-plugin_' . $plugin_file ) . '">' . __( 'You can still force deactivation by clicking here.', 'rocket' ) . '</a>';
			}
			break;
	}

	?>
	<div class="notice notice-<?php echo $args['status']; ?> <?php echo $args['dismissible']; ?>">
		<?php 
			$tag = 0 !== strpos( $message, '<p' ) && 0 !== strpos( $message, '<ul' );

			echo ( $tag ? '<p>' : '' ) . $args['message'] . ( $tag ? '</p>' : '' );
		?>
		<?php if ( ! empty( $args['readonly_content'] ) ) : ?>
		<p><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="6"><?php echo esc_textarea( $args['readonly_content'] ); ?></textarea></p>
		<?php endif;
		if ( $action || $args['dismiss_button'] ) : ?>
		<p>
			<?php echo $action; ?>
			<?php if ( $args['dismiss_button'] ) : ?>
			<a class="rocket-dismiss" href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box=' . __FUNCTION__ ), 'rocket_ignore_' . __FUNCTION__ ); ?>"><?php _e( 'Dismiss this notice.', 'rocket' ); ?></a>
			<?php endif; ?>
		</p>
		<?php endif; ?>
	</div>
	<?php
}
