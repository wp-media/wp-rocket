<?php

defined( 'ABSPATH' ) || exit;

/**
 * This warnings are displayed when the plugin can not be deactivated correctly
 *
 * @since 2.0.0
 */
function rocket_bad_deactivations() {
	global $current_user;

	$msgs = get_transient( $current_user->ID . '_donotdeactivaterocket' );
	if ( current_user_can( 'rocket_manage_options' ) && $msgs ) {

		delete_transient( $current_user->ID . '_donotdeactivaterocket' );
		$errors = [];

		foreach ( $msgs as $msg ) {
			switch ( $msg ) {
				case 'wpconfig':
					$errors['wpconfig'] = '<p>' . sprintf(
						// translators: %1$s WP Rocket plugin name; %2$s = file name.
						__(
							'<strong>%1$s</strong> has not been deactivated due to missing writing permissions.<br>
Make <strong>%2$s</strong> writeable and retry deactivation, or force deactivation now:',
							'rocket'
							),
						WP_ROCKET_PLUGIN_NAME,
						'wp-config.php'
					) . '</p>';
					break;

				case 'htaccess':
					$errors['htaccess'] = '<p>' . sprintf(
						// translators: %1$s WP Rocket plugin name; %2$s = file name.
						__(
							'<strong>%1$s</strong> has not been deactivated due to missing writing permissions.<br>
Make <strong>%2$s</strong> writeable and retry deactivation, or force deactivation now:',
							'rocket'
							),
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

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => implode( '', $errors ),
				'action'      => 'force_deactivation',
			]
		);
	}
}
add_action( 'admin_notices', 'rocket_bad_deactivations' );

/**
 * This warning is displayed to inform the user that a plugin de/activation can be followed by a cache clear
 *
 * @since 1.3.0
 */
function rocket_warning_plugin_modification() {
	if ( current_user_can( 'rocket_manage_options' ) && rocket_valid_key() ) {

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		rocket_notice_html(
			[
				'status'         => 'warning',
				'dismissible'    => '',
				// translators: %s is WP Rocket plugin name.
				'message'        => sprintf( __( '<strong>%s</strong>: One or more plugins have been enabled or disabled, clear the cache if they affect the front end of your site.', 'rocket' ), WP_ROCKET_PLUGIN_NAME ),
				'action'         => 'clear_cache',
				'dismiss_button' => __FUNCTION__,
			]
		);
	}
}
add_action( 'admin_notices', 'rocket_warning_plugin_modification' );

/**
 * This warning is displayed when some plugins may conflict with WP Rocket
 *
 * @since 1.3.0
 */
function rocket_plugins_to_deactivate() {
	$plugins              = [];
	$plugins_explanations = [];

	// Deactivate all plugins who can cause conflicts with WP Rocket.
	$plugins = [
		'w3-total-cache'                             => 'w3-total-cache/w3-total-cache.php',
		'wp-super-cache'                             => 'wp-super-cache/wp-cache.php',
		'litespeed-cache'                            => 'litespeed-cache/litespeed-cache.php',
		'quick-cache'                                => 'quick-cache/quick-cache.php',
		'hyper-cache'                                => 'hyper-cache/plugin.php',
		'hyper-cache-extended'                       => 'hyper-cache-extended/plugin.php',
		'wp-fast-cache'                              => 'wp-fast-cache/wp-fast-cache.php',
		'flexicache'                                 => 'flexicache/wp-plugin.php',
		'wp-fastest-cache'                           => 'wp-fastest-cache/wpFastestCache.php',
		'lite-cache'                                 => 'lite-cache/plugin.php',
		'gator-cache'                                => 'gator-cache/gator-cache.php',
		'cache-enabler'                              => 'cache-enabler/cache-enabler.php',
		'swift-performance-lite'                     => 'swift-performance-lite/performance.php',
		'swift-performance'                          => 'swift-performance/performance.php',
		'speed-booster-pack'                         => 'speed-booster-pack/speed-booster-pack.php',
		'wp-http-compression'                        => 'wp-http-compression/wp-http-compression.php',
		'wordpress-gzip-compression'                 => 'wordpress-gzip-compression/ezgz.php',
		'gzip-ninja-speed-compression'               => 'gzip-ninja-speed-compression/gzip-ninja-speed.php',
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
		'enable-gzip-compression'                    => 'enable-gzip-compression/enable-gzip-compression.php',
		'leverage-browser-caching'                   => 'leverage-browser-caching/leverage-browser-caching.php',
		'add-expires-headers'                        => 'add-expires-headers/add-expires-headers.php',
		'page-optimize'                              => 'page-optimize/page-optimize.php',
		'psn-pagespeed-ninja'                        => 'psn-pagespeed-ninja/pagespeedninja.php',
	];

	if ( get_rocket_option( 'lazyload' ) ) {
		$plugins['bj-lazy-load']              = 'bj-lazy-load/bj-lazy-load.php';
		$plugins['lazy-load']                 = 'lazy-load/lazy-load.php';
		$plugins['jquery-image-lazy-loading'] = 'jquery-image-lazy-loading/jq_img_lazy_load.php';
		$plugins['advanced-lazy-load']        = 'advanced-lazy-load/advanced_lazyload.php';
		$plugins['crazy-lazy']                = 'crazy-lazy/crazy-lazy.php';
		$plugins['specify-image-dimensions']  = 'specify-image-dimensions/specify-image-dimensions.php';
	}

	if ( get_rocket_option( 'lazyload_iframes' ) ) {
		$plugins['lazy-load-for-videos'] = 'lazy-load-for-videos/codeispoetry.php';
	}

	if ( get_rocket_option( 'minify_css' ) || get_rocket_option( 'minify_js' ) ) {
		$plugins['wp-super-minify']         = 'wp-super-minify/wp-super-minify.php';
		$plugins['bwp-minify']              = 'bwp-minify/bwp-minify.php';
		$plugins['wp-minify']               = 'wp-minify/wp-minify.php';
		$plugins['scripts-gzip']            = 'scripts-gzip/scripts_gzip.php';
		$plugins['minqueue']                = 'minqueue/plugin.php';
		$plugins['dependency-minification'] = 'dependency-minification/dependency-minification.php';
		$plugins['fast-velocity-minify']    = 'fast-velocity-minify/fvm.php';
	}

	if ( get_rocket_option( 'minify_css' ) || get_rocket_option( 'minify_js' ) ) {
		$plugins['async-js-and-css']     = 'async-js-and-css/asyncJSandCSS.php';
		$plugins['merge-minify-refresh'] = 'merge-minify-refresh/merge-minify-refresh.php';
	}

	if ( get_rocket_option( 'minify_js' ) ) {
		$plugins['wp-js']                = 'wp-js/wp-js.php';
		$plugins['combine-js']           = 'combine-js/combine-js.php';
		$plugins['footer-javascript']    = 'footer-javascript/footer-javascript.php';
		$plugins['scripts-to-footerphp'] = 'scripts-to-footerphp/scripts-to-footer.php';
	}

	if ( get_rocket_option( 'do_cloudflare' ) ) {
		$plugins['cloudflare']              = 'cloudflare/cloudflare.php';
		$plugins_explanations['cloudflare'] = __( 'WP Rocket Cloudflare Add-on provides similar functionalities. They can not be active at the same time.', 'rocket' );
	}

	if ( get_rocket_option( 'control_heartbeat' ) ) {
		$plugins['heartbeat-control'] = 'heartbeat-control/heartbeat-control.php';
	}

	/**
	 * Filter the recommended plugins to deactivate to prevent conflicts
	 *
	 * @since 2.6.4
	 *
	 * @param array $plugins List of recommended plugins to deactivate.
	 */
	$plugins = apply_filters( 'rocket_plugins_to_deactivate', $plugins );

	/**
	 * Filter the recommended plugins to deactivate explanations
	 *
	 * @since 3.10.5
	 *
	 * @param array $plugins List of recommended plugins to deactivate explanations.
	 */
	$plugins_explanations = apply_filters( 'rocket_plugins_to_deactivate_explanations', $plugins_explanations );

	$plugins = array_filter( $plugins, 'is_plugin_active' );

	if ( current_user_can( 'rocket_manage_options' )
		&& count( $plugins )
		&& rocket_valid_key()
	) {

		// translators: %s is WP Rocket plugin name.
		$warning = '<p>' . sprintf( __( '<strong>%s</strong>: The following plugins are not compatible with this plugin and may cause unexpected results:', 'rocket' ), WP_ROCKET_PLUGIN_NAME ) . '</p>';

		$warning .= '<ul class="rocket-plugins-error">';

		foreach ( $plugins as $k => $plugin ) {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin );
			$warning    .= '<li><b>' . $plugin_data['Name'] . '</b>' . ( isset( $plugins_explanations[ $k ] ) ? ' - ' . $plugins_explanations[ $k ] : '' ) . '</span> <a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=deactivate_plugin&plugin=' . rawurlencode( $plugin ) ), 'deactivate_plugin' ) . '" class="button-secondary alignright">' . __( 'Deactivate', 'rocket' ) . '</a></li>';
		}

		$warning .= '</ul>';

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $warning,
			]
		);
	}
}
add_action( 'admin_notices', 'rocket_plugins_to_deactivate' );

/**
 * Displays a warning if Rocket Footer JS plugin is active
 *
 * @since 3.2.3
 * @author Remy Perona
 *
 * @return void
 */
function rocket_warning_footer_js_plugin() {
	$screen = get_current_screen();

	if ( ! current_user_can( 'rocket_manage_options' ) ) {
		return;
	}

	if ( 'settings_page_wprocket' !== $screen->id ) {
		return;
	}

	if ( ! is_plugin_active( 'rocket-footer-js/rocket-footer-js.php' ) ) {
		return;
	}

	rocket_notice_html(
		[
			'status'         => 'warning',
			'message'        => __( 'WP Rocket Footer JS is not an official add-on. It prevents some options in WP Rocket from working correctly. Please deactivate it if you have problems.', 'rocket' ),
			'dismiss_button' => true,
		]
	);
}
add_action( 'admin_notices', 'rocket_warning_footer_js_plugin' );

/**
 * Display a warning if Endurance Cache is not disabled
 *
 * @since 3.3.7
 * @author Remy Perona
 *
 * @return void
 */
function rocket_warning_endurance_cache() {
	$screen = get_current_screen();

	// This filter is documented in inc/admin-bar.php.
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		return;
	}

	if ( 'settings_page_wprocket' !== $screen->id ) {
		return;
	}

	if ( ! class_exists( 'Endurance_Page_Cache' ) ) {
		return;
	}

	if ( 0 === (int) get_option( 'endurance_cache_level' ) ) {
		return;
	}

	rocket_notice_html(
		[
			'status'  => 'error',
			'message' => sprintf(
				// translators: %1$s = opening link tag, %2$s = closing link tag.
				__( 'Endurance Cache is currently enabled, which will conflict with WP Rocket Cache. Please set the Endurance Cache cache level to Off (Level 0) on the %1$sSettings > General%2$s page to prevent any issues.', 'rocket' ),
				'<a href="' . admin_url( 'options-general.php#epc_settings' ) . '">',
				'</a>'
			),
		]
	);
}
add_action( 'admin_notices', 'rocket_warning_endurance_cache' );

/**
 * This warning is displayed when there is no permalink structure in the configuration.
 *
 * @since 1.0
 */
function rocket_warning_using_permalinks() {
	if ( current_user_can( 'rocket_manage_options' )
		&& ! $GLOBALS['wp_rewrite']->using_permalinks()
		&& rocket_valid_key()
	) {
		$message = sprintf(
			/* translators: %1$s WP Rocket plugin name; %2$s = opening link; %3$s = closing link */
			__( '%1$s: A custom permalink structure is required for the plugin to work properly. %2$sGo to permalinks settings%3$s', 'rocket' ),
			'<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>',
			'<a href="' . admin_url( 'options-permalink.php' ) . '">',
			'</a>'
		);

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
			]
		);
	}
}
add_action( 'admin_notices', 'rocket_warning_using_permalinks' );

/**
 * This warning is displayed when the .htaccess file doesn't exist or isn't writeable
 *
 * @since 1.0
 */
function rocket_warning_htaccess_permissions() {
	global $is_apache;
	$htaccess_file = get_home_path() . '.htaccess';

	if ( ! current_user_can( 'rocket_manage_options' )
		|| ( rocket_direct_filesystem()->is_writable( $htaccess_file ) )
		|| ! $is_apache
		// This filter is documented in inc/functions/htaccess.php.
		|| apply_filters( 'rocket_disable_htaccess', false )
		|| ! rocket_valid_key() ) {
			return;
	}

	if ( rocket_check_htaccess_rules() ) {
		return;
	}

	$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

	if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
		return;
	}

	$message = sprintf(
		// translators: %s = plugin name.
		__( '%s could not modify the .htaccess file due to missing writing permissions.', 'rocket' ),
		'<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>'
	);

	$message .= '<br>' . sprintf(
		/* translators: This is a doc title! %1$s = opening link; %2$s = closing link */
		__( 'Troubleshoot: %1$sHow to make system files writeable%2$s', 'rocket' ),
		/* translators: Documentation exists in EN, DE, FR, ES, IT; use loaclised URL if applicable */
		'<a href="' . __( 'https://docs.wp-rocket.me/article/626-how-to-make-system-files-htaccess-wp-config-writeable/?utm_source=wp_plugin&utm_medium=wp_rocket', 'rocket' ) . '" target="_blank">',
		'</a>'
	);

	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 42 );

	$message .= '<p>' . __( 'Don’t worry, WP Rocket’s page caching and settings will still function correctly.', 'rocket' ) . '<br>' . __( 'For optimal performance, adding the following lines into your .htaccess is recommended (not required):', 'rocket' ) . '<br><textarea readonly="readonly" id="rocket_htaccess_rules" name="rocket_htaccess_rules" class="large-text readonly" rows="6">' . esc_textarea( get_rocket_htaccess_marker() ) . '</textarea></p>';

	remove_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 42 );

	rocket_notice_html(
		[
			'status'         => 'warning',
			'dismissible'    => '',
			'message'        => $message,
			'dismiss_button' => __FUNCTION__,
		]
	);
}
add_action( 'admin_notices', 'rocket_warning_htaccess_permissions' );

/**
 * This warning is displayed when the config dir isn't writeable
 *
 * @since 2.0.2
 */
function rocket_warning_config_dir_permissions() {
	if ( current_user_can( 'rocket_manage_options' )
		&& ( ! rocket_direct_filesystem()->is_writable( WP_ROCKET_CONFIG_PATH ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = rocket_notice_writing_permissions( trim( str_replace( ABSPATH, '', WP_ROCKET_CONFIG_PATH ), '/' ) );

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
			]
		);

	}
}
add_action( 'admin_notices', 'rocket_warning_config_dir_permissions' );

/**
 * This warning is displayed when the cache dir isn't writeable
 *
 * @since 1.0
 */
function rocket_warning_cache_dir_permissions() {
	if ( current_user_can( 'rocket_manage_options' )
		&& ( ! rocket_direct_filesystem()->is_writable( WP_ROCKET_CACHE_PATH ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = rocket_notice_writing_permissions( trim( str_replace( ABSPATH, '', WP_ROCKET_CACHE_PATH ), '/' ) );

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
			]
		);
	}
}
add_action( 'admin_notices', 'rocket_warning_cache_dir_permissions' );

/**
 * This warning is displayed when the minify cache dir isn't writeable
 *
 * @since 2.1
 */
function rocket_warning_minify_cache_dir_permissions() {
	if ( current_user_can( 'rocket_manage_options' )
		&& ( ! rocket_direct_filesystem()->is_writable( WP_ROCKET_MINIFY_CACHE_PATH ) )
		&& ( get_rocket_option( 'minify_css', false ) || get_rocket_option( 'minify_js', false ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = rocket_notice_writing_permissions( trim( str_replace( ABSPATH, '', WP_ROCKET_MINIFY_CACHE_PATH ), '/' ) );

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
			]
		);
	}
}
add_action( 'admin_notices', 'rocket_warning_minify_cache_dir_permissions' );

/**
 * This warning is displayed when the busting cache dir isn't writeable
 *
 * @since 2.9
 */
function rocket_warning_busting_cache_dir_permissions() {
	if ( current_user_can( 'rocket_manage_options' )
		&& ( ! rocket_direct_filesystem()->is_writable( WP_ROCKET_CACHE_BUSTING_PATH ) )
		&& rocket_valid_key() ) {

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = rocket_notice_writing_permissions( trim( str_replace( ABSPATH, '', WP_ROCKET_CACHE_BUSTING_PATH ), '/' ) );

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
			]
		);
	}
}
add_action( 'admin_notices', 'rocket_warning_busting_cache_dir_permissions' );

/**
 * Confirming notice when the site has been added
 *
 * @since 2.2
 */
function rocket_thank_you_license() {
	if ( '1' === get_rocket_option( 'license' ) ) {
		$options            = get_option( WP_ROCKET_SLUG );
		$options['license'] = time();
		$options['ignore']  = true;
		update_option( WP_ROCKET_SLUG, $options );

		$message = sprintf(
			/* translators: %1$s = plugin name, %2$s + %3$s = opening links, %4$s = closing link */
			__( '%1$s is good to go! %2$sTest your load time%4$s, or visit your %3$ssettings%4$s.', 'rocket' ),
			'<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>',
			'<a href="https://wp-rocket.me/blog/correctly-measure-websites-page-load-time/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">',
			'<a href="' . admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ) . '">',
			'</a>'
		);

		rocket_notice_html( [ 'message' => $message ] );
	}
}
add_action( 'admin_notices', 'rocket_thank_you_license' );

/**
 * This notice is displayed after purging OPcache
 *
 * @since 3.4.1
 * @author Soponar Cristina
 */
function rocket_opcache_purge_result() {
	if ( ! current_user_can( 'rocket_purge_opcache' ) ) {
		return;
	}

	if ( ! is_admin() ) {
		return;
	}

	$user_id = get_current_user_id();
	$notice  = get_transient( $user_id . '_opcache_purge_result' );
	if ( ! $notice ) {
		return;
	}

	delete_transient( $user_id . '_opcache_purge_result' );

	rocket_notice_html(
		[
			'status'  => $notice['result'],
			'message' => $notice['message'],
		]
	);
}
add_action( 'admin_notices', 'rocket_opcache_purge_result' );

/**
 * Displays a notice for analytics opt-in
 *
 * @since 2.11
 * @author Remy Perona
 */
function rocket_analytics_optin_notice() {

	$screen = get_current_screen();

	if ( ! current_user_can( 'rocket_manage_options' ) ) {
		return;
	}

	if ( 'settings_page_wprocket' !== $screen->id ) {
		return;
	}

	if ( 1 === (int) get_option( 'rocket_analytics_notice_displayed' ) ) {
		return;
	}

	if ( get_rocket_option( 'analytics_enabled' ) ) {
		return;
	}

	$analytics_notice = sprintf(
		// Opening <p> provided by rocket_notice_html().
		'<strong>%1$s</strong><br>%2$s</p>',
		__( 'Would you allow WP Rocket to collect non-sensitive diagnostic data from this website?', 'rocket' ),
		__( 'This would help us to improve WP Rocket for you in the future.', 'rocket' )
	);

	$analytics_notice .= sprintf(
		'<p><button class="hide-if-no-js button-rocket-reveal rocket-preview-analytics-data">%s</button></p>',
		/* translators: button text, click will expand data collection preview */
		__( 'What info will we collect?', 'rocket' )
	);

	$analytics_notice .= sprintf(
		'<div class="rocket-analytics-data-container"><p class="description">%1$s</p>%2$s</div>',
		__( 'Below is a detailed view of all data WP Rocket will collect if granted permission. WP Rocket will never transmit any domain names or email addresses (except for license validation), IP addresses, or third-party API keys.', 'rocket' ),
		rocket_data_collection_preview_table()
	);

	$analytics_notice .= sprintf(
		'<p><a href="%1$s" class="button button-primary">%2$s</a> <a href="%3$s" class="button button-secondary">%4$s</a>',
		// Closing </p> provided by rocket_notice_html().
		wp_nonce_url( admin_url( 'admin-post.php?action=rocket_analytics_optin&value=yes' ), 'analytics_optin' ),
		/* translators: button text for data collection opt-in */
		__( 'Yes, allow', 'rocket' ),
		wp_nonce_url( admin_url( 'admin-post.php?action=rocket_analytics_optin&value=no' ), 'analytics_optin' ),
		/* translators: button text for data collection opt-in */
		__( 'No, thanks', 'rocket' )
	);

	// Status should be as neutral as possible; nothing has happened yet.
	rocket_notice_html(
		[
			'status'  => 'info',
			'message' => $analytics_notice,
		]
	);
}
add_action( 'admin_notices', 'rocket_analytics_optin_notice' );

/**
 * Displays a notice after analytics opt-in
 *
 * @since 2.11
 * @author Remy Perona
 */
function rocket_analytics_optin_thankyou_notice() {
	$screen = get_current_screen();

	if ( ! current_user_can( 'rocket_manage_options' ) ) {
		return;
	}

	if ( 'settings_page_wprocket' !== $screen->id ) {
		return;
	}

	$analytics_optin = get_transient( 'rocket_analytics_optin' );

	if ( ! $analytics_optin ) {
		return;
	}

	$thankyou_message = sprintf(
		// Opening <p> provided by rocket_notice_html().
		'<strong>%s</strong></p>',
		__( 'Thank you!', 'rocket' )
	);

	$thankyou_message .= sprintf(
		'<p>%1$s</p><div>%2$s</div>',
		__( 'WP Rocket now collects these metrics from your website:', 'rocket' ),
		rocket_data_collection_preview_table()
	);

	// Closing </p> provided by rocket_notice_html().
	$thankyou_message .= '<p>';

	rocket_notice_html(
		[
			'message' => $thankyou_message,
		]
	);

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
	$cleared_cache = get_transient( 'rocket_clear_cache' );

	if ( ! $cleared_cache ) {
		return;
	}

	delete_transient( 'rocket_clear_cache' );

	$notice = '';

	switch ( $cleared_cache ) {
		case 'all':
			if ( current_user_can( 'rocket_purge_cache' ) ) {
				// translators: %s = plugin name.
				$notice  = sprintf( __( '%s: Cache cleared.', 'rocket' ), '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>' );
				$notice .= '<em> (' . date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) ) . ') </em>';
			}
			break;
		case 'post':
			if ( current_user_can( 'rocket_purge_posts' ) ) {
				// translators: %s = plugin name.
				$notice  = sprintf( __( '%s: Post cache cleared.', 'rocket' ), '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>' );
				$notice .= '<em> (' . date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) ) . ') </em>';
			}
			break;
		case 'term':
			if ( current_user_can( 'rocket_purge_terms' ) ) {
				// translators: %s = plugin name.
				$notice  = sprintf( __( '%s: Term cache cleared.', 'rocket' ), '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>' );
				$notice .= '<em> (' . date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) ) . ') </em>';
			}
			break;
		case 'user':
			if ( current_user_can( 'rocket_purge_users' ) ) {
				// translators: %s = plugin name).
				$notice  = sprintf( __( '%s: User cache cleared.', 'rocket' ), '<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>' );
				$notice .= '<em> (' . date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) ) . ') </em>';
			}
			break;
		default:
			break;
	}

	if ( empty( $notice ) ) {
		return;
	}

	rocket_notice_html(
		[
			'message' => $notice,
		]
	);
}
add_action( 'admin_notices', 'rocket_clear_cache_notice' );

/**
 * Outputs notice HTML
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @param array $args An array of arguments used to determine the notice output.
 * @return void
 */
function rocket_notice_html( $args ) {
	$defaults = [
		'status'           => 'success',
		'dismissible'      => 'is-dismissible',
		'message'          => '',
		'action'           => '',
		'dismiss_button'   => false,
		'readonly_content' => '',
	];

	$args = wp_parse_args( $args, $defaults );

	switch ( $args['action'] ) {
		case 'clear_cache':
			$args['action'] = '<a class="wp-core-ui button" href="' . wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=all' ), 'purge_cache_all' ) . '">' . __( 'Clear cache', 'rocket' ) . '</a>';
			break;
		case 'stop_preload':
			$args['action'] = '<a class="wp-core-ui button" href="' . wp_nonce_url( admin_url( 'admin-post.php?action=rocket_stop_preload&type=all' ), 'rocket_stop_preload' ) . '">' . __( 'Stop Preload', 'rocket' ) . '</a>';
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
				$plugin_file  = 'wp-rocket/wp-rocket.php';
				$rocket_nonce = wp_create_nonce( 'force_deactivation' );

				$args['action'] = '<a href="' . wp_nonce_url( 'plugins.php?action=deactivate&amp;rocket_nonce=' . $rocket_nonce . '&amp;plugin=' . $plugin_file . '&amp;plugin_status=' . $status . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-plugin_' . $plugin_file ) . '">' . __( 'Force deactivation ', 'rocket' ) . '</a>';
			}
			break;
	}

	?>
	<div class="notice notice-<?php echo esc_attr( $args['status'] ); ?> <?php echo esc_attr( $args['dismissible'] ); ?>">
		<?php
			$tag = 0 !== strpos( $args['message'], '<p' ) && 0 !== strpos( $args['message'], '<ul' );

			echo ( $tag ? '<p>' : '' ) . $args['message'] . ( $tag ? '</p>' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
		?>
		<?php if ( ! empty( $args['readonly_content'] ) ) : ?>
		<p><?php esc_html_e( 'The following code should have been written to this file:', 'rocket' ); ?>
			<br><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="6"><?php echo esc_textarea( $args['readonly_content'] ); ?></textarea>
		</p>
			<?php
		endif;
		if ( $args['action'] || $args['dismiss_button'] ) :
			?>
		<p>
			<?php echo $args['action']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $args['dismiss_button'] ) : ?>
			<a class="rocket-dismiss" href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box=' . $args['dismiss_button'] ), 'rocket_ignore_' . $args['dismiss_button'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php esc_html_e( 'Dismiss this notice.', 'rocket' ); ?></a>
			<?php endif; ?>
		</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Outputs formatted notice about issues with writing permissions
 *
 * @since  2.11
 * @author Caspar Hübinger
 *
 * @param  string $file File or folder name.
 * @return string       Message HTML
 */
function rocket_notice_writing_permissions( $file ) {

	$message = sprintf(
		// translators: %s = plugin name.
		__( '%s cannot configure itself due to missing writing permissions.', 'rocket' ),
		'<strong>' . WP_ROCKET_PLUGIN_NAME . '</strong>'
	);

	$message .= '<br>' . sprintf(
		/* translators: %s = file/folder name */
		__( 'Affected file/folder: %s', 'rocket' ),
		'<code>' . $file . '</code>'
	);

	$message .= '<br>' . sprintf(
		/* translators: This is a doc title! %1$s = opening link; %2$s = closing link */
		__( 'Troubleshoot: %1$sHow to make system files writeable%2$s', 'rocket' ),
		/* translators: Documentation exists in EN, DE, FR, ES, IT; use loaclised URL if applicable */
		'<a href="' . __( 'https://docs.wp-rocket.me/article/626-how-to-make-system-files-htaccess-wp-config-writeable/?utm_source=wp_plugin&utm_medium=wp_rocket', 'rocket' ) . '" target="_blank">',
		'</a>'
	);

	return $message;
}
